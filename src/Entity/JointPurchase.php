<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * Совместные закупки (JointPurchase)
 *
 * @ORM\Entity()
 * @ORM\Table(name="joint_purchases",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *      }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class JointPurchase
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\Description;

    const STATUS_DRAFT      = 0;
    const STATUS_OPEN       = 1;
    const STATUS_CLOSE      = 2;
    const STATUS_COMPLETE   = 3;
    static protected $status_values = [
        self::STATUS_DRAFT      => 'Черновик',
        self::STATUS_OPEN       => 'Открыто',
        self::STATUS_CLOSE      => 'Закрыто',
        self::STATUS_COMPLETE   => 'Завершено',
    ];

    const SHIPPING_TYPE_NONE     = 0;
    const SHIPPING_TYPE_PER_ITEM = 1;
    const SHIPPING_TYPE_PERCENT  = 2;
    static protected $shipping_type_values = [
        self::SHIPPING_TYPE_NONE     => 'Нет',
        self::SHIPPING_TYPE_PER_ITEM => 'Оплата за еденицу товара',
        self::SHIPPING_TYPE_PERCENT  => 'Процент от заказа',
    ];

    /**
     * Срок формирования заказа (финальная дата) он же “стоп”
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=false)
     */
    protected $final_date;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":0})
     */
    protected $status;

    /**
     * Тип лоставки
     *
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "default":0})
     *
     * @deprecated
     */
    protected $shipping_type;

    /**
     * Транспортные расходы в процентах
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $transportation_cost_in_percent;

    /**
     * Ссылка на чат в телеграм
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $telegram_chat_link;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $organizer;

    /**
     * @var JointPurchaseProduct[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="JointPurchaseProduct", mappedBy="joint_purchase", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $products;

    /**
     * @var JointPurchaseOrder[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="JointPurchaseOrder", mappedBy="joint_purchase", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created_at" = "DESC"})
     */
    protected $orders;

    /**
     * JointPurchase constructor.
     */
    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->final_date   = new \DateTime('+3 days');
        $this->orders       = new ArrayCollection();
        $this->products     = new ArrayCollection();
        $this->shipping_type = self::SHIPPING_TYPE_NONE;
        $this->status       = self::STATUS_DRAFT;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdated()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getFinalDate(): \DateTime
    {
        return $this->final_date;
    }

    /**
     * @param \DateTime $final_date
     *
     * @return $this
     */
    public function setFinalDate(\DateTime $final_date): self
    {
        $this->final_date = $final_date;

        return $this;
    }

    /**
     * @return User
     */
    public function getOrganizer(): User
    {
        return $this->organizer;
    }

    /**
     * @param User $organizer
     *
     * @return $this
     */
    public function setOrganizer(User $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return JointPurchaseProduct[]|ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return int|null
     */
    public function getTransportationCostInPercent(): ?int
    {
        return $this->transportation_cost_in_percent;
    }

    /**
     * @param int|null $transportation_cost_in_percent
     *
     * @return $this
     */
    public function setTransportationCostInPercent(?int $transportation_cost_in_percent): self
    {
        $this->transportation_cost_in_percent = $transportation_cost_in_percent;

        return $this;
    }

    /**
     * @return JointPurchaseOrder[]|ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param JointPurchaseOrder[]|ArrayCollection $orders
     *
     * @return $this
     */
    public function setOrders($orders): self
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTelegramChatLink(): ?string
    {
        return $this->telegram_chat_link;
    }

    /**
     * Получение ссылки для прямого вызова действия в телеграм
     *
     * @return string|null
     */
    public function getTelegramChatLinkAction(): ?string
    {
        if (!empty($this->telegram_chat_link)) {
            return "tg://join?invite=".str_replace('https://t.me/joinchat/', '', $this->telegram_chat_link);
        }

        return null;
    }

    /**
     * @param string|null $telegram_chat_link
     *
     * @return $this
     */
    public function setTelegramChatLink(?string $telegram_chat_link): self
    {
        if (!empty($telegram_chat_link) and stripos($telegram_chat_link, 'https://t.me/joinchat/') !== 0) {
            $telegram_chat_link = null;
        }

        $this->telegram_chat_link = $telegram_chat_link;

        return $this;
    }

    // Start STATUS block of setters and getters

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
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    // __End STATUS block of setters and getters

    // Start "ShippingType" block of setters and getters

    /**
     * @return int
     *
     * @deprecated
     */
    public function getShippingType(): int
    {
        return $this->shipping_type;
    }

    /**
     * @param int $shipping_type
     *
     * @return $this
     *
     * @deprecated
     */
    public function setShippingType(int $shipping_type): self
    {
        $this->shipping_type = $shipping_type;

        return $this;
    }

    /**
     * @return array
     */
    static public function getShippingTypeFormChoices(): array
    {
        return array_flip(self::$shipping_type_values);
    }

    /**
     * @return array
     */
    static public function getShippingTypeValues(): array
    {
        return self::$shipping_type_values;
    }

    /**
     * @return bool
     */
    static public function isShippingTypeExist($shipping_type): bool
    {
        if (isset(self::$shipping_type_values[$shipping_type])) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getShippingTypeAsText(): string
    {
        if (isset(self::$shipping_type_values[$this->shipping_type])) {
            return self::$shipping_type_values[$this->shipping_type];
        }

        return 'N/A';
    }

    // __End "ShippingType" block of setters and getters
}
