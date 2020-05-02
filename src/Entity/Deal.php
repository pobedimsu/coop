<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\StatusTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DealRepository")
 * @ORM\Table(name="deals",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"updated_at"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Deal
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\Comment;

    use StatusTrait;

    const STATUS_NEW                    = 0;
    const STATUS_VIEW                   = 1;
    const STATUS_ACCEPTED               = 2;
    const STATUS_ACCEPTED_OUTSIDE       = 3;
    const STATUS_COMPLETE               = 4;
    const STATUS_COMPLETE_OUTSIDE       = 5;
    const STATUS_CANCEL_BY_DECLARANT    = 6;
    const STATUS_CANCEL_BY_CONTRACTOR   = 7;
    static protected $status_values = [
        self::STATUS_NEW                    => 'Новая',
        self::STATUS_VIEW                   => 'Просмотрено',
        self::STATUS_ACCEPTED               => 'Принято',
        self::STATUS_ACCEPTED_OUTSIDE       => 'Принято для совершения вне системы',
        self::STATUS_COMPLETE               => 'Завершено',
        self::STATUS_COMPLETE_OUTSIDE       => 'Завершено вне системы',
        self::STATUS_CANCEL_BY_DECLARANT    => 'Отменено заявителем',
        self::STATUS_CANCEL_BY_CONTRACTOR   => 'Отменено исполнителем',
    ];

    /**
     * Стоимость предложения на момент создания сделки.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected $cost;

    /**
     * Фактическая стоимость - до которой договорились участники.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected $actual_cost;

    /**
     * Общая стоимость - кол-во помноженное на фактическую стоимость.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected $amount_cost;

    /**
     * Кол-во
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $quantity;

    /**
     * Дата просмотра
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $viewed_at;

    /**
     * @var Offer
     *
     * @ORM\ManyToOne(targetEntity="Offer")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $offer;

    /**
     * Подрядчик, исполнитель, продавец
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $contractor_user;

    /**
     * Заявитель, покупатель
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $declarant_user;

    /**
     * @var Transaction[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="deal", fetch="EXTRA_LAZY")
     */
    protected $transactions;

    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->status       = self::STATUS_NEW;
        $this->transactions = new ArrayCollection();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdated()
    {
        $this->updated_at = new \DateTime();
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost($cost): Deal
    {
        $this->cost = $cost;

        return $this;
    }

    public function getActualCost(): int
    {
        return $this->actual_cost;
    }

    public function setActualCost($actual_cost): self
    {
        $this->actual_cost = $actual_cost;

        return $this;
    }

    public function getAmountCost(): int
    {
        return $this->amount_cost;
    }

    public function setAmountCost($amount_cost): self
    {
        $this->amount_cost = $amount_cost;

        return $this;
    }

    public function getQuantity(): int
    {
        return (int) $this->quantity;
    }

    public function setQuantity($quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getViewedAt(): ?\DateTime
    {
        return $this->viewed_at;
    }

    public function setViewedAt($viewed_at): self
    {
        $this->viewed_at = $viewed_at;

        return $this;
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }

    public function setOffer(Offer $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getContractorUser(): User
    {
        return $this->contractor_user;
    }

    public function setContractorUser(User $contractor_user): self
    {
        $this->contractor_user = $contractor_user;

        return $this;
    }

    public function getDeclarantUser(): User
    {
        return $this->declarant_user;
    }

    public function setDeclarantUser(User $declarant_user): self
    {
        $this->declarant_user = $declarant_user;

        return $this;
    }

    /**
     * @return Transaction[]|Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param Transaction[]|Collection $transactions
     *
     * @return $this
     */
    public function setTransactions($transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }
}
