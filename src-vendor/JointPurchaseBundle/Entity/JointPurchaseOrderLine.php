<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * Зваказы по товарам в заказе совместных закупок.
 *
 * @ORM\Entity()
 * @ORM\Table(name="joint_purchases_orders_lines",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *      }, uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"product_id", "order_id"}),
 *      }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class JointPurchaseOrderLine
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\Comment;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected $quantity;

    /**
     * Цена за еденицу товара на момент подачи заявки
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected $price;

    /**
     * @var JointPurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="JointPurchaseOrder", inversedBy="lines")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $order;

    /**
     * @var JointPurchaseProduct
     *
     * @ORM\ManyToOne(targetEntity="JointPurchaseProduct", inversedBy="order_lines")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $product;

    /**
     * JointPurchaseUser constructor.
     */
    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->quantity     = 1;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->product->getTitle();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdated()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return JointPurchaseProduct
     */
    public function getProduct(): JointPurchaseProduct
    {
        return $this->product;
    }

    /**
     * @param JointPurchaseProduct $product
     *
     * @return $this
     */
    public function setProduct(JointPurchaseProduct $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return $this
     */
    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return JointPurchaseOrder
     */
    public function getOrder(): JointPurchaseOrder
    {
        return $this->order;
    }

    /**
     * @param JointPurchaseOrder $order
     *
     * @return $this
     */
    public function setOrder(JointPurchaseOrder $order): self
    {
        $this->order = $order;

        return $this;
    }
}
