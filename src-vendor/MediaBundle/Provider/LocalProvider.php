<?php

namespace SmartCore\Bundle\MediaBundle\Provider;

use Doctrine\ORM\EntityManager;
use Liip\ImagineBundle\Model\FileBinary;
use SmartCore\Bundle\MediaBundle\Entity\Collection;
use SmartCore\Bundle\MediaBundle\Entity\File;
use SmartCore\Bundle\MediaBundle\Entity\FileTransformed;
use SmartCore\Bundle\MediaBundle\Service\GeneratorService;
use SmartCore\Bundle\MediaBundle\Service\MediaCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class LocalProvider implements ProviderInterface
{
    use ContainerAwareTrait;

    /** @var string  */
    protected $original_dir;

    /** @var string */
    protected $filter_dir;

    /** @var EntityManager */
    protected $em;

    /** @var GeneratorService  */
    protected $generator;

    /** @var Request */
    protected $request;

    /**
     * Используется только метод generateRelativePath
     *
     * @var MediaCollection
     *
     * @deprecated
     */
    protected $mediaCollection;

    /**
     * LocalProvider constructor.
     *
     * @param ContainerInterface $container
     * @param array              $arguments
     */
    public function __construct(ContainerInterface $container, array $arguments = [])
    {
        if (isset($arguments['filter_dir'])) {
            $this->filter_dir = $arguments['filter_dir'];
        } else {
            $this->filter_dir = $container->getParameter('kernel.project_dir').'/public';
        }

        if (isset($arguments['original_dir'])) {
            $this->original_dir = $arguments['original_dir'];
        } else {
            $this->original_dir = $container->getParameter('kernel.project_dir').'/public';
        }

        $this->container    = $container;
        $this->em           = $container->get('doctrine.orm.entity_manager');
        $this->generator    = $container->get('smart_media.generator');
        $this->request      = $container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @return bool
     */
    public function isSupportFilter(): bool
    {
        return true;
    }

    /**
     * Получить ссылку на файл.
     *
     * @param int $id
     * @param string|null $filter
     * @param string|null default_filter
     *
     * @return string|null
     */
    public function get($id, $filter = null, $default_filter = '200x200')
    {
        if (empty($filter)) {
            $filter = null;
        }

        if (null === $id) {
            return null;
        }

        /** @var File $file */
        $file = $this->em->find(File::class, $id);

        if (null === $file) {
            return null;
        }

        if ($file and $file->isMimeType('png')) {
            $runtimeConfig['format'] = 'png';
        }

        try {
            $this->container->get('liip_imagine.filter.configuration')->get($filter);
//            $this->container->get('smart_imagine_configuration')->get($filter);
        } catch (\RuntimeException $e) {
            if ($filter !== 'orig') {
                try {
                    $this->container->get('liip_imagine.filter.configuration')->get($default_filter);
//                    $this->container->get('smart_imagine_configuration')->get($default_filter);

                    $filter = $default_filter;
                } catch (\RuntimeException $e) {
                    $filter = null;
                }
            } else {
                $filter = null;
            }
        }

        $basePath = $this->request ? $this->request->getBasePath() : '';
        $ending   = '';

        if ($filter) {
            $fileTransformed = $this->em->getRepository(FileTransformed::class)->findOneBy(['file' => $file, 'filter' => $filter]);

            if (isset($runtimeConfig['format'])) {
                $ending = '.'.$runtimeConfig['format'];
            } else {
                $ending = '.'.$this->container->get('liip_imagine.filter.configuration')->get($filter)['format'];
//                $ending = '.'.$this->container->get('smart_imagine_configuration')->get($filter)['format'];
            }

            if (null === $fileTransformed) {
                //$ending .= '?id='.$file->getId();
                return $basePath.
                    $file->getStorage()->getRelativePath(). // @todo !!!
                    $file->getCollection()->getRelativePath(). // @todo !!!
                    '/'.$filter.'/img.php?id='.$file->getId()
                ;
            }
        }

        $transformedImagePathInfo = pathinfo($basePath.$file->getFullRelativeUrl($filter));

        if (empty($ending)) {
            $ending = '.'.$transformedImagePathInfo['extension'];
        }

        return $transformedImagePathInfo['dirname'].'/'.$transformedImagePathInfo['filename'].$ending;
    }

    /**
     * @param int    $id
     * @param string $filter
     *
     * @return null|mixed
     *
     * ok
     */
    public function generateTransformedFile(int $id, $filter)
    {
        /** @var File $file */
        $file = $this->em->find(File::class, $id);

        if (null === $file) {
            return null;
        }

        $runtimeConfig = [];
        if ($file and $file->isMimeType('png')) {
            $runtimeConfig['format'] = 'png';
        }

        $fileTransformed = $this->em->getRepository(FileTransformed::class)->findOneBy(['file' => $file, 'filter' => $filter]);

//        if (null === $fileTransformed) {

            if ($file->isMimeType('image/jpeg') or $file->isMimeType('image/png') or $file->isMimeType('image/gif')) {
                // dummy
            } else {
                echo 'Unsupported image format';

                return null;
            }

            $path_orig = $this->getFilePath($file, 'orig');

            $path = $this->getFileTransformedPath($file, $filter);

            if (!is_dir(dirname($path)) and false === @mkdir(dirname($path), 0777, true)) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory.\n", dirname($path)));
            }

            $originalImage = new FileBinary($path_orig, $file->getMimeType(), ExtensionGuesser::getInstance()->guess($file->getMimeType()));

            $imagineFilterManager = $this->container->get('liip_imagine.filter.manager');
            $transformedImage = $imagineFilterManager->applyFilter($originalImage, $filter, $runtimeConfig)->getContent();

            file_put_contents($path, $transformedImage);

            if (null === $fileTransformed) {
                $fileTransformed = new FileTransformed();
                $fileTransformed
                    ->setFile($file)
                    ->setFilter($filter)
                    ->setSize((new \SplFileInfo($path))->getSize())
                ;

                $this->em->persist($fileTransformed);
                $this->em->flush();
            }

            return $transformedImage;
//        }

//        return null;
    }
    
    /**
     * @param File $file
     *
     * @return \Symfony\Component\HttpFoundation\File\File|void
     *
     * @throws \RuntimeException
     *
     * ok
     */
    public function upload(File $file, $relative_path)
    {
        $upload_dir = $this->original_dir.$relative_path;

        if (!is_dir($upload_dir) and false === @mkdir($upload_dir, 0777, true)) {
            throw new \RuntimeException(sprintf("Unable to create the %s directory.\n", $upload_dir));
        }

        $newFile = $file->getUploadedFile()->move($upload_dir, $file->getFilename());

        return $newFile;
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @todo обработку ошибок.
     *
     * ok
     */
    public function remove($id)
    {
        /** @var File $file */
        $file = $this->em->find(File::class, $id);

        if (!$file) {
            return false;
        }

        /** @var FileTransformed $fileTransformed */
        foreach ($file->getFilesTransformed() as $fileTransformed) {
            $fullPath = $this->getFileTransformedPath($file, $fileTransformed->getFilter());

            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        // Удаление оригинала.
        $fullPath = $this->getFilePath($file, 'orig');

        @unlink($fullPath);

        return true;
    }

    /**
     * @param File $file
     * @param null $filter
     *
     * @return string
     *
     * ok
     */
    public function getFilePath(File $file, $filter = null): string
    {
        $path = $this->original_dir.$this->mediaCollection->generateRelativePath($filter);
        $path = str_replace('{user_id}', $file->getUserId(), $path);
        $path .= $file->getRelativePath().'/'.$file->getFilename();

        return $path;
    }

    /**
     * @param File $file
     * @param null $filter
     *
     * @return string
     *
     * ok
     */
    public function getFileTransformedPath(File $file, $filter = null): string
    {
        $path = $this->filter_dir.$this->mediaCollection->generateRelativePath($filter);
        $path = str_replace('{user_id}', $file->getUserId(), $path);
        $path .= $file->getRelativePath().'/'.$file->getFilename();

        return $path;
    }

    /**
     * @param $collection
     *
     * @return bool
     *
     * ok
     */
    public function purgeTransformedFiles($collection)
    {
        foreach ($this->container->get('smart_imagine_configuration')->all() as $filter_name => $filter) {
            $dir = $this->filter_dir.'/'.$filter_name;

            if (is_dir($dir)) {
                foreach(new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path
                ) {
                    $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
                }

                rmdir($dir);
            }
        }

        return true;
    }

    /**
     * Получить список файлов.
     *
     * @param int|null $categoryId
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return File[]|null
     */
    public function findBy($categoryId = null, array $orderBy = null, $limit = null, $offset = null)
    {
        // @todo
    }

    /**
     * @return MediaCollection
     */
    public function getMediaCollection(): MediaCollection
    {
        return $this->mediaCollection;
    }

    /**
     * @param MediaCollection $mediaCollection
     *
     * @return $this
     */
    public function setMediaCollection(MediaCollection $mediaCollection): self
    {
        $this->mediaCollection = $mediaCollection;

        return $this;
    }
}
