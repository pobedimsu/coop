<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InviteRepository")
 * @ORM\Table(name="invites",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"is_used"}),
 *      },
 * )
 */
class Invite
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\User;

    /**
     * Использована ли ссылка. Если по приглашению зарегистрировался участник, то ставится true.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    protected $is_used;

    /**
     * Invite constructor.
     *
     * @param User|null $user
     */
    public function __construct(User $user = null)
    {
        $this->created_at = new \DateTime();

        if ($user) {
            $this->setUser($user);
        }
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->is_used;
    }

    /**
     * @return bool
     */
    public function getIsUsed(): bool
    {
        return $this->is_used;
    }

    /**
     * @param bool $is_used
     *
     * @return $this
     */
    public function setIsUsed(bool $is_used): self
    {
        $this->is_used = $is_used;

        return $this;
    }
}
