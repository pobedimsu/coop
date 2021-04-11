<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * Спрос
 *
 * @ORM\Entity(repositoryClass="App\Repository\DemandRepository")
 * @ORM\Table(name="demands",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *      },
 * )
 */
class Demand
{
    use ColumnTrait\Uuid;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\Description;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;

    /**
     * ИД файла в медиалибе
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $image_id = null;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected User $user;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getImageId(): ?int
    {
        return $this->image_id;
    }

    public function setImageId(?int $image_id): self
    {
        $this->image_id = $image_id;

        return $this;
    }
}
