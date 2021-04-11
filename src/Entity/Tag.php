<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="tags")
 */
class Tag
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\Description;
    use ColumnTrait\User;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected string $title;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
