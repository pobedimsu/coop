<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="FileRepository")
 * @ORM\Table(name="media_files",
 *      indexes={
 *          @ORM\Index(columns={"collection"}),
 *          @ORM\Index(columns={"storage"}),
 *          @ORM\Index(columns={"size"}),
 *          @ORM\Index(columns={"type"}),
 *          @ORM\Index(columns={"user_id"}),
 *      }
 * )
 */
class File
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2)
     */
    protected $collection;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="files")
     */
    protected $category;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2)
     */
    protected $storage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $relative_path;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $original_filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=8)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    protected $mime_type;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $original_size;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $size;

    /**
     * @var FileTransformed[]
     *
     * @ORM\OneToMany(targetEntity="FileTransformed", mappedBy="file", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $filesTransformed;

    /**
     * @var UploadedFile
     */
    protected $uploadedFile;

    /**
     * File constructor.
     *
     * @param \Symfony\Component\HttpFoundation\File\File|null $uploadedFile
     */
    public function __construct(\Symfony\Component\HttpFoundation\File\File $uploadedFile = null)
    {
        $this->created_at   = new \DateTime();
        $this->storage      = null;

        if ($uploadedFile) {
            $this->uploadedFile = $uploadedFile;

            // @todo video и т.д
            if (false !== strpos($uploadedFile->getMimeType(), 'image/')) {
                $this->setType('image');
            } else {
                $this->setType($uploadedFile->getType());
            }

            if ($uploadedFile instanceof UploadedFile) {
                $this->setOriginalFilename($uploadedFile->getClientOriginalName());
            } else {
                $this->setOriginalFilename($uploadedFile->getFilename());
            }

            $this->setMimeType($uploadedFile->getMimeType());
            $this->setOriginalSize($uploadedFile->getSize());
            $this->setSize($uploadedFile->getSize());
        }
    }

    /**
     * @param string|null $filter
     *
     * @return string
     *
     * @deprecated
     */
    public function getFullRelativeUrl($filter = null)
    {
        return $this->getFullRelativePath($filter).'/'.$this->getFilename();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return FileTransformed[]
     */
    public function getFilesTransformed()
    {
        return $this->filesTransformed;
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     *
     * @return $this
     */
    public function setCollection(string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return string
     */
    public function getStorage(): string
    {
        return $this->storage;
    }

    /**
     * @param string $storage
     *
     * @return $this
     */
    public function setStorage(string $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
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
     * @param string $originalFilename
     *
     * @return $this
     */
    public function setOriginalFilename($originalFile)
    {
        $this->original_filename = $originalFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->original_filename;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $mimeType
     *
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mime_type = $mimeType;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * @param int $original_size
     *
     * @return $this
     */
    public function setOriginalSize($original_size)
    {
        $this->original_size = $original_size;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalSize()
    {
        return $this->original_size;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isMimeType($type)
    {
        if (strpos($this->getMimeType(), $type) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     *
     * @return $this
     */
    public function setUserId($userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
