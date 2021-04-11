<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @ORM\Table(name="transactions",
 *      indexes={
 *          @ORM\Index(columns={"sum"}),
 *          @ORM\Index(columns={"created_at"}),
 *      }, uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"hash"}),
 *      }
 * )
 */
class Transaction
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Comment;

    /**
     * От кого переходит сумма (покупатель)
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $from_user;

    /**
     * Кому переходит сумма (продавец)
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $to_user;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true, "default":0})
     */
    protected int $sum;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected ?string $hash;

    /**
     * Сделка на основании которой произведена транзакция
     *
     * @ORM\ManyToOne(targetEntity="Deal", inversedBy="transactions")
     */
    protected Deal $deal;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->hash       = null;
    }

    public function getSum(): int
    {
        return $this->sum;
    }

    public function setSum($sum): self
    {
        $this->sum = $sum;

        return $this;
    }

    public function getDeal(): Deal
    {
        return $this->deal;
    }

    public function setDeal(Deal $deal): self
    {
        $this->deal = $deal;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getFromUser(): User
    {
        return $this->from_user;
    }

    public function setFromUser(User $from_user): self
    {
        $this->from_user = $from_user;

        return $this;
    }

    public function getToUser(): User
    {
        return $this->to_user;
    }

    public function setToUser(User $to_user): self
    {
        $this->to_user = $to_user;

        return $this;
    }
}
