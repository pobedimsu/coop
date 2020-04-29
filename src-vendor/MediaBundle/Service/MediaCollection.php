<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Liip\ImagineBundle\Model\FileBinary;
use SmartCore\Bundle\MediaBundle\Entity\Category;
use SmartCore\Bundle\MediaBundle\Entity\Collection;
use SmartCore\Bundle\MediaBundle\Entity\File;
use SmartCore\Bundle\MediaBundle\Entity\FileTransformed;
use SmartCore\Bundle\MediaBundle\Provider\LocalProvider;
use SmartCore\Bundle\MediaBundle\Provider\ProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

class MediaCollection extends AbstractCollectionService
{
    use ContainerAwareTrait;

    protected $code;
    protected $title;
    protected $relative_path;

    /**
     * @var MediaStorage
     *
     * @deprecated
     */
    protected $storage;

    protected $file_relative_path_pattern;
    protected $filename_pattern;

    protected $user_id;

    /**
     * @param ContainerInterface $container
     * @param int|null $id
     */
    public function __construct(ContainerInterface $container = null, $id = null)
    {
        parent::__construct($container->get('doctrine.orm.entity_manager'));

        $this->container = $container;

        $this->default_filter = null;

        if ($container->has('security.token_storage')
            and $container->get('security.token_storage')->getToken()
            and method_exists($container->get('security.token_storage')->getToken()->getUser(), 'getId')
        ) {
            $this->user_id = $container->get('security.token_storage')->getToken()->getUser()->getId();
        } else{
            $this->user_id = null;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param Category|int $category
     * @param array $tags
     *
     * @return int - ID файла в коллекции.
     */
    public function upload(\Symfony\Component\HttpFoundation\File\File $file, $category = null, array $tags = null)
    {
        $tmp_default_filter = $this->getDefaultFilter();

        $this->setDefaultFilter(null);

        if ($this->getUploadFilter()) {
            $imagineFilterManager = $this->container->get('liip_imagine.filter.manager');

            if ($file->getMimeType() == 'image/jpeg' or $file->getMimeType() == 'image/png' or $file->getMimeType() == 'image/gif') {
                // dummy
            } else {
                echo 'Unsupported image format';

                return null;
            }

            $fileBinary = new FileBinary($file->getPathname(), $file->getMimeType(), ExtensionGuesser::getInstance()->guess($file->getMimeType()));

            $runtimeConfig = [];
            if ($file->getMimeType() == 'image/png') {
                $runtimeConfig['format'] = 'png';
            }

            $transformedImage = $imagineFilterManager->applyFilter($fileBinary, $this->getUploadFilter(), $runtimeConfig)->getContent();

            if ($file instanceof UploadedFile) {
                $tmp_uploaded_file = $this->container->getParameter('kernel.cache_dir').'/'.$file->getClientOriginalName();
            } else {
                $tmp_uploaded_file = $this->container->getParameter('kernel.cache_dir').'/'.$file->getFilename();
            }

            file_put_contents($tmp_uploaded_file, $transformedImage);

            $file = new \Symfony\Component\HttpFoundation\File\File($tmp_uploaded_file);
        }

        // @todo проверку на доступность загруженного файла
        // могут быть проблеммы, если в настройках сервера указан маленький upload_max_filesize и/или post_max_size
        $fileEntity = new File($file);
        $fileEntity
            ->setCollection($this->getCode())
            ->setRelativePath($this->generateFilePath())
            ->setFilename($this->generateFileName($fileEntity))
            ->setUserId($this->user_id)
            ->setStorage($this->storage->getCode())
        ;

        $newFile = $this->getProvider()->upload($fileEntity, $this->generatePattern($this->generateRelativePath().$this->generateFilePath()));

        $this->setDefaultFilter($tmp_default_filter);

        $this->em->persist($fileEntity);
        $this->em->flush();

        return $fileEntity->getId();
    }

    /**
     * @param string|null $filter
     *
     * @return string
     */
    public function generateRelativePath($filter = null)
    {
        $relativePath = $this->getStorage()->getRelativePath();

        if (!$filter) {
            $filter = $this->getDefaultFilter();
        }

        if (empty($filter)) {
            $filter = 'orig';
        }

        return $relativePath.'/'.$filter.$this->getRelativePath();
    }

    /**
     * @param string|null $filter
     *
     * @return string
     *
     * @deprecated
     */
    public function getFullRelativePath($filter = null)
    {
        $relativePath = $this->getStorage()->getRelativePath().$this->getRelativePath();

        if (empty($filter)) {
            $filter = 'orig';
        }

        return $relativePath.'/'.$filter;
    }

    /**
     * @return string
     */
    public function generateFilePath(): string
    {
        return $this->generatePattern($this->getFileRelativePathPattern());
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public function generateFileName(File $file)
    {
        $filename = $this->getFilenamePattern();

        //return $this->generatePattern($filename.'.'.$file->getUploadedFile()->getClientOriginalExtension());
        return $this->generatePattern($filename.'.'.$file->getUploadedFile()->getFileInfo()->getExtension()); // @todo
    }

    /**
     * @param string|null $pattern
     *
     * @return mixed|string
     */
    public function generatePattern($pattern = null)
    {
        $pattern = str_replace('{year}',     date('Y'), $pattern);
        $pattern = str_replace('{month}',    date('m'), $pattern);
        $pattern = str_replace('{day}',      date('d'), $pattern);
        $pattern = str_replace('{hour}',     date('H'), $pattern);
        $pattern = str_replace('{minutes}',  date('i'), $pattern);
        $pattern = str_replace('{user_id}',  $this->user_id, $pattern);
        $pattern = str_replace('{rand(10)}', substr(md5(microtime(true).uniqid()), 0, 10), $pattern);

        return $pattern;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     *
     * @return $this
     */
    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     *
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelativePath()
    {
        return $this->relative_path;
    }

    /**
     * @param mixed $relative_path
     *
     * @return $this
     */
    public function setRelativePath($relative_path): self
    {
        $this->relative_path = $relative_path;

        return $this;
    }

    /**
     * @return MediaStorage
     */
    public function getStorage(): MediaStorage
    {
        return $this->storage;
    }

    /**
     * @param MediaStorage $storage
     *
     * @return $this
     */
    public function setStorage(MediaStorage $storage): self
    {
        $this->storage = $storage;
        //$this->setProvider($storage->getProvider()); // @todo

        $this->provider = $storage->factoryProvider();
        $this->provider->setMediaCollection($this); // @todo

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileRelativePathPattern()
    {
        return $this->file_relative_path_pattern;
    }

    /**
     * @param mixed $file_relative_path_pattern
     *
     * @return $this
     */
    public function setFileRelativePathPattern($file_relative_path_pattern): self
    {
        $this->file_relative_path_pattern = $file_relative_path_pattern;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilenamePattern()
    {
        return $this->filename_pattern;
    }

    /**
     * @param mixed $filename_pattern
     *
     * @return $this
     */
    public function setFilenamePattern($filename_pattern): self
    {
        $this->filename_pattern = $filename_pattern;

        return $this;
    }
}
