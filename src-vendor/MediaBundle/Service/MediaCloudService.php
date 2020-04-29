<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Service;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\MediaBundle\Entity\Collection;
use SmartCore\Bundle\MediaBundle\Entity\File;
use SmartCore\Bundle\MediaBundle\Entity\Storage;
use SmartCore\Bundle\MediaBundle\Provider\ProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaCloudService
{
    use ContainerAwareTrait;

    protected $config;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var MediaCollection[]
     */
    protected $collections;

    /**
     * @var MediaStorage[]
     */
    protected $storages;

    /**
     * MediaCloudService constructor.
     *
     * @param ContainerInterface     $container
     * @param EntityManagerInterface $em
     * @param array                  $config
     */
    public function __construct(ContainerInterface $container, EntityManagerInterface $em, array $config)
    {
        $this->config = $config;

        // storages
        foreach ($config['storages'] as $name => $val) {
            $ms = new MediaStorage();
            $ms->setCode($val['code'])
                ->setTitle($val['title'])
                ->setRelativePath($val['relative_path'])
                ->setArguments($val['arguments'])
                ->setProviderClass($val['provider'])
                ->setContainer($container)
            ;

            $this->storages[$val['code']] = $ms;
        }

        try {
            $dbStorages = $em->getRepository(Storage::class)->findAll();

            foreach ($dbStorages as $dbStorage) {
                if (isset($this->storages[$dbStorage->getCode()])) {
                    throw new \Exception('Storage with code "'.$dbStorage->getCode().'" is already exist');
                }

                $s = new MediaStorage();
                $s->setCode($dbStorage->getCode())
                    ->setTitle($dbStorage->getTitle())
                    ->setRelativePath($dbStorage->getRelativePath())
                    ->setProviderClass($dbStorage->getProvider())
                    ->setArguments($dbStorage->getArguments())
                    ->setContainer($container)
                ;

                $this->storages[$dbStorage->getCode()] = $s;
            }
        } catch (TableNotFoundException $e) {
            // @todo
        }

        // collections
        foreach ($config['collections'] as $name => $val) {
            $mc = new MediaCollection($container);
            $mc->setCode($val['code'])
                ->setTitle($val['title'])
                ->setRelativePath($val['relative_path'])
                ->setDefaultFilter($val['default_filter'])
                ->setUploadFilter($val['upload_filter'])
                ->setFilenamePattern($val['filename_pattern'])
                ->setFileRelativePathPattern($val['file_relative_path_pattern'])
                ->setStorage($this->storages[$val['storage']])
            ;

            $this->collections[$val['code']] = $mc;
        }

        try {
            $dbCollections = $em->getRepository(Collection::class)->findAll();
            foreach ($dbCollections as $dbCollection) {
                if (isset($this->collections[$dbCollection->getCode()])) {
                    throw new \Exception('Collection with code "'.$dbCollection->getCode().'" is already exist');
                }

                $mc = new MediaCollection($container);
                $mc->setCode($dbCollection->getCode())
                    ->setTitle($dbCollection->getTitle())
                    ->setRelativePath($dbCollection->getRelativePath())
                    ->setDefaultFilter($dbCollection->getDefaultFilter())
                    ->setUploadFilter($dbCollection->getUploadFilter())
                    ->setFilenamePattern($dbCollection->getFilenamePattern())
                    ->setFileRelativePathPattern($dbCollection->getFileRelativePathPattern())
                    ->setStorage($this->storages[$dbCollection->getStorage()->getCode()])
                ;

                $this->collections[$dbCollection->getCode()] = $mc;
            }
        } catch (TableNotFoundException $e) {
            // @todo
        }

        $this->container = $container;
        $this->em        = $em;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->em;
    }

    /**
     * @param int|null $id
     *
     * @return MediaCollection
     *
     * @todo если не задан id, и коллекций больше 1, то выкидывать исключение.
     */
    public function getCollection($code = null)
    {
        return $this->collections[$code];
    }

    /**
     * Получить ссылку на файл.
     *
     * @param int $id
     * @param string $filter
     *
     * @return string|null
     *
     * @todo кеширование.
     */
    public function getFileUrl($id, $filter = null)
    {
        if (!is_numeric($id)) {
            return null;
        }

        /** @var File $file */
        $file = $this->em->getRepository(File::class)->find($id);

        if (empty($file)) {
            return null;
        }

        return $this->getCollection($file->getCollection())->get($id, $filter);
    }

    /**
     * @param int    $id
     * @param string $filter
     *
     * @return mixed|null
     */
    public function generateTransformedFile(int $id, $filter)
    {
        if (!is_numeric($id)) {
            return null;
        }

        /** @var File $file */
        $file = $this->em->getRepository(File::class)->find($id);

        if (empty($file)) {
            return null;
        }

        return $this->getCollection($file->getCollection()->getId())->generateTransformedFile($id, $filter);
    }

    public function createCollection()
    {
        // @todo
    }

    public function removeCollection()
    {
        // @todo
    }

    /**
     * @return MediaCollection[]|array
     */
    public function getCollections(): array
    {
        return $this->collections;
    }

    public function getStoragesList()
    {
        // @todo
    }

    public function createStorage()
    {
        // @todo
    }

    public function removeStorage()
    {
        // @todo
    }

    public function updateStorage()
    {
        // @todo
    }
}
