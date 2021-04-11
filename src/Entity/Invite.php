<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * Приглашения участников
 *
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
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    protected bool $is_used;

    public function __construct(User $user = null)
    {
        $this->created_at = new \DateTime();
        $this->is_used = false;

        if ($user) {
            $this->setUser($user);
        }
    }

    public function isUsed(): bool
    {
        return $this->is_used;
    }

    public function getIsUsed(): bool
    {
        return $this->is_used;
    }

    public function setIsUsed(bool $is_used): self
    {
        $this->is_used = $is_used;

        return $this;
    }
}
