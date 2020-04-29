<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\MediaBundle\Entity\Category;
use SmartCore\Bundle\MediaBundle\Entity\File;
use SmartCore\Bundle\MediaBundle\Provider\ProviderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractCollectionService
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var GeneratorService */
    protected $generator;

    /** @var ProviderInterface */
    protected $provider;

    /** @var string|null */
    protected $default_filter;

    /** @var string|null */
    protected $upload_filter;

    /**
     * AbstractCollectionService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->generator = new GeneratorService();
    }

    /**
     * @param ProviderInterface $provider
     *
     * @return $this
     */
    public function setProvider(ProviderInterface $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param EntityManagerInterface $em
     *
     * @return $this
     */
    public function setEntityManager(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }

    /**
     * Получить ссылку на файл.
     *
     * @param int         $id
     * @param string|null $filter
     *
     * @return string|null
     */
    public function get($id, $filter = null)
    {
        return $this->provider->get($id, $filter, $this->getDefaultFilter());
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function remove($id)
    {
        if (empty($id)) {
            return;
        }

        $this->getProvider()->remove($id);

        $file = $this->em->find(File::class, $id);

        if (!empty($file)) {
            $this->em->remove($file);
            $this->em->flush();
        }

        return true;
    }

    /**
     * @param int    $id
     * @param string $filter
     *
     * @return mixed|null
     */
    public function generateTransformedFile(int $id, $filter)
    {
        return $this->getProvider()->generateTransformedFile($id, $filter);
    }

    /**
     * @return bool
     */
    public function purgeTransformedFiles()
    {
        return $this->getProvider()->purgeTransformedFiles($this);
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
     * @return string|null
     */
    public function getDefaultFilter(): ?string
    {
        return $this->default_filter;
    }

    /**
     * @param string|null $default_filter
     *
     * @return $this
     */
    public function setDefaultFilter(?string $default_filter): self
    {
        $this->default_filter = $default_filter;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUploadFilter(): ?string
    {
        return $this->upload_filter;
    }

    /**
     * @param string|null $upload_filter
     *
     * @return $this
     */
    public function setUploadFilter(?string $upload_filter): self
    {
        $this->upload_filter = $upload_filter;

        return $this;
    }
}
