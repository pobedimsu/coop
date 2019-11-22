<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BillRepository")
 * @ORM\Table(name="bills",
 *      indexes={
 *          @ORM\Index(columns={"sum"}),
 *          @ORM\Index(columns={"created_at"}),
 *      }, uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"hash"}),
 *      }
 * )
 */
class Bill
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Comment;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $balance;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $sum;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $hash;

    /**
     * @var Deal
     *
     * @ORM\ManyToOne(targetEntity="Deal", inversedBy="bills")
     */
    protected $deal;

    /**
     * Bill constructor.
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->balance    = 0;
        $this->hash       = null;
    }

    /**
     * @return int
     */
    public function getBalance(): int
    {
        return $this->balance;
    }

    /**
     * @param int $balance
     *
     * @return $this
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
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
}
