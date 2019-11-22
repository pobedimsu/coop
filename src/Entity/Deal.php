<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":0})
     */
    protected $status;

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
     */
    protected $offer;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $contractor_user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $declarant_user;

    /**
     * @var Bill[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Bill", mappedBy="deal", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $bills;

    /**
     * Deal constructor.
     */
    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->status       = self::STATUS_NEW;
        $this->bills        = new ArrayCollection();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdated()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @return array
     */
    static public function getStatusFormChoices(): array
    {
        return array_flip(self::$status_values);
    }

    /**
     * @return array
     */
    static public function getStatusValues(): array
    {
        return self::$status_values;
    }

    /**
     * @return bool
     */
    static public function isStatusExist($status): bool
    {
        if (isset(self::$status_values[$status])) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStatusAsText(): string
    {
        if (isset(self::$status_values[$this->status])) {
            return self::$status_values[$this->status];
        }

        return 'N/A';
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status): Deal
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getCost(): int
    {
        return $this->cost;
    }

    /**
     * @param int $cost
     *
     * @return $this
     */
    public function setCost($cost): Deal
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return int
     */
    public function getActualCost(): int
    {
        return $this->actual_cost;
    }

    /**
     * @param int $actual_cost
     *
     * @return $this
     */
    public function setActualCost($actual_cost): self
    {
        $this->actual_cost = $actual_cost;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmountCost(): int
    {
        return $this->amount_cost;
    }

    /**
     * @param int $amount_cost
     *
     * @return $this
     */
    public function setAmountCost($amount_cost): self
    {
        $this->amount_cost = $amount_cost;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getViewedAt(): ?\DateTime
    {
        return $this->viewed_at;
    }

    /**
     * @param \DateTime $viewed_at
     *
     * @return $this
     */
    public function setViewedAt($viewed_at): self
    {
        $this->viewed_at = $viewed_at;

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     *
     * @return $this
     */
    public function setOffer(Offer $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @return User
     */
    public function getContractorUser(): User
    {
        return $this->contractor_user;
    }

    /**
     * @param User $contractor_user
     *
     * @return $this
     */
    public function setContractorUser(User $contractor_user): self
    {
        $this->contractor_user = $contractor_user;

        return $this;
    }

    /**
     * @return User
     */
    public function getDeclarantUser(): User
    {
        return $this->declarant_user;
    }

    /**
     * @param User $declarant_user
     *
     * @return $this
     */
    public function setDeclarantUser(User $declarant_user): self
    {
        $this->declarant_user = $declarant_user;

        return $this;
    }
}
