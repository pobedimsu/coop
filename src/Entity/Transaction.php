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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $from_user;

    /**
     * Кому переходит сумма (продавец)
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $to_user;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true, "default":0})
     */
    protected $sum;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $hash;

    /**
     * Сделка на основании которой произведена транзакция
     *
     * @var Deal
     *
     * @ORM\ManyToOne(targetEntity="Deal", inversedBy="transactions")
     */
    protected $deal;

    /**
     * Transaction constructor.
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->hash       = null;
    }

    /**
     * @return int
     */
    public function getSum(): int
    {
        return $this->sum;
    }

    /**
     * @param int $sum
     *
     * @return $this
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * @return Deal
     */
    public function getDeal(): Deal
    {
        return $this->deal;
    }

    /**
     * @param Deal $deal
     *
     * @return $this
     */
    public function setDeal($deal)
    {
        $this->deal = $deal;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param null|string $hash
     *
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return User
     */
    public function getFromUser(): User
    {
        return $this->from_user;
    }

    /**
     * @param User $from_user
     *
     * @return $this
     */
    public function setFromUser(User $from_user): self
    {
        $this->from_user = $from_user;

        return $this;
    }

    /**
     * @return User
     */
    public function getToUser(): User
    {
        return $this->to_user;
    }

    /**
     * @param User $to_user
     *
     * @return $this
     */
    public function setToUser(User $to_user): self
    {
        $this->to_user = $to_user;

        return $this;
    }
}
