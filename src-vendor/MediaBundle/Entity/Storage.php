<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use SmartCore\Bundle\MediaBundle\Provider\ProviderInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="media_storages")
 * @UniqueEntity(fields={"code"}, message="Code must be unique")
 */
class Storage
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Title;

    /**
     * Уникальный код хранилища
     *
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=false, unique=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $relative_path;

    /**
     * @var string instanceof ProviderInterface
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $provider;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $arguments;

    /**
     * @param string $relativePath
     */
    public function __construct($relativePath = '')
    {
        $this->created_at       = new \DateTime();
        $this->relative_path    = $relativePath;
        $this->title            = 'Новое хранилище';
        $this->provider         = 'SmartCore\Bundle\MediaBundle\Provider\LocalProvider';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
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
     * @return array
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
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
     * @param string $provider
     *
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
