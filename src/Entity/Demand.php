<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
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
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $image_id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * Demand constructor.
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImageId(): ?int
    {
        return $this->image_id;
    }

    /**
     * @param int|null $image_id
     *
     * @return $this
     */
    public function setImageId(?int $image_id): self
    {
        $this->image_id = $image_id;

        return $this;
    }
}
