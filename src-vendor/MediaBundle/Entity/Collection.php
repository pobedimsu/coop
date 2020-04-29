<?php

namespace SmartCore\Bundle\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_collections")
 * @UniqueEntity(fields={"code"}, message="Code must be unique")
 */
class Collection
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\TitleNotBlank;

    /**
     * Уникальный код коллекции
     *
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=false, unique=true)
     */
    protected $code;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $default_filter;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $upload_filter;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     *
     * @deprecated
     */
    protected $params;

    /**
     * @var Storage
     *
     * @ORM\ManyToOne(targetEntity="Storage", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $storage;

    /**
     * Относительный путь можно менять, только если нету файлов в коллекции
     * либо предусмотреть как-то переименовывание пути в провайдере.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $relative_path;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $file_relative_path_pattern;

    /**
     * Маска имени файла. Если пустая строка, то использовать оригинальное имя файла,
     * совместимое с вебформатом т.е. без пробелов и русских букв.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     */
    protected $filename_pattern;

    /**
     * @param string $relativePath
     */
    public function __construct($relativePath = '')
    {
        $this->created_at       = new \DateTime();
        $this->relative_path    = $relativePath;

        $this->filename_pattern            = '{hour}_{minutes}_{rand(10)}';
        $this->file_relative_path_pattern  = '/{year}/{month}/{day}';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->title;
    }

    /**
     * @return Storage
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * @param Storage $storage
     *
     * @return $this
     */
    public function setStorage(Storage $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @param string $default_filter
     *
     * @return $this
     */
    public function setDefaultFilter($default_filter)
    {
        $this->default_filter = $default_filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultFilter()
    {
        return $this->default_filter;
    }

    /**
     * @param array|null $params
     *
     * @return $this
     */
    public function setParams(array $params = null)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $file_relative_path_pattern
     *
     * @return $this
     */
    public function setFileRelativePathPattern($file_relative_path_pattern)
    {
        $this->file_relative_path_pattern = $file_relative_path_pattern;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileRelativePathPattern()
    {
        return $this->file_relative_path_pattern;
    }

    /**
     * @param string $filename_pattern
     *
     * @return $this
     */
    public function setFilenamePattern($filename_pattern)
    {
        $this->filename_pattern = $filename_pattern;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilenamePattern()
    {
        return $this->filename_pattern;
    }

    /**
     * @param string $relative_path
     *
     * @return $this
     */
    public function setRelativePath($relative_path)
    {
        $this->relative_path = $relative_path;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relative_path;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

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
