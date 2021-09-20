<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\StatusTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * Сделки
 *
 * @ORM\Entity(repositoryClass="App\Repository\DealRepository")
 * @ORM\Table(name="deals",
 *      indexes={
 *          @ORM\Index(columns={"amount_cost"}),
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"updated_at"}),
 *          @ORM\Index(columns={"status"}),
 *          @ORM\Index(columns={"type"}),
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

    const TYPE_EXTERNAL = 0;
    const TYPE_INNER    = 1;

    const STATUS_NEW                    = 0;
    const STATUS_VIEW                   = 1;
    const STATUS_ACCEPTED               = 2;
    const STATUS_ACCEPTED_EXTERNAL      = 3;
    const STATUS_COMPLETE               = 4;
    const STATUS_COMPLETE_OUTSIDE       = 5;
    const STATUS_CANCEL_BY_BUYER        = 6;
    const STATUS_CANCEL_BY_SELLER       = 7;
    static protected $status_values = [
        self::STATUS_NEW                => 'Новая',
        self::STATUS_VIEW               => 'Просмотрено',
        self::STATUS_ACCEPTED           => 'Принято',
        self::STATUS_ACCEPTED_EXTERNAL  => 'Принято для совершения вне системы',
        self::STATUS_COMPLETE           => 'Завершено',
        self::STATUS_COMPLETE_OUTSIDE   => 'Завершено вне системы',
        self::STATUS_CANCEL_BY_BUYER    => 'Отменено покупателем',
        self::STATUS_CANCEL_BY_SELLER   => 'Отменено продавцом',
    ];

    /**
     * @ORM\Column(type="smallint", nullable=false, options={"default":1})
     */
    protected int $type;

    /**
     * Стоимость предложения на момент создания сделки.
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected int $cost;

    /**
     * Фактическая стоимость - до которой договорились участники.
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected int $actual_cost;

    /**
     * Общая стоимость - кол-во помноженное на фактическую стоимость.
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected int $amount_cost;

    /**
     * Кол-во
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $quantity;

    /**
     * Дата просмотра
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $viewed_at;

    /**
     * @var Offer
     *
     * @ORM\ManyToOne(targetEntity="Offer")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $offer;

    /**
     * Ппродавец
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $seller;

    /**
     * Покупатель
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $buyer;

    /**
     * @var Transaction[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="deal", fetch="EXTRA_LAZY")
     */
    protected Collection $transactions;

    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->status       = self::STATUS_NEW;
        $this->transactions = new ArrayCollection();
        $this->type         = self::TYPE_INNER;
    }

    public function getTypeText(): string
    {
        switch ($this->type) {
            case self::TYPE_EXTERNAL:
                return 'Внешняя';
            case self::TYPE_INNER:
                return 'Внутренняя';
            default:
                throw new \ErrorException('Не допустимый тип сделки: ' . $this->type);
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdated(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): Deal
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

    public function setAmountCost(int $amount_cost): self
    {
        $this->amount_cost = $amount_cost;

        return $this;
    }

    public function getQuantity(): int
    {
        return (int) $this->quantity;
    }

    public function setQuantity(int $quantity): self
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

    public function getBuyer(): User
    {
        return $this->buyer;
    }

    public function setBuyer(User $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * @return Transaction[]|Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @param Transaction[]|Collection $transactions
     */
    public function setTransactions($transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }

    public function getSeller(): User
    {
        return $this->seller;
    }

    public function setSeller(User $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
