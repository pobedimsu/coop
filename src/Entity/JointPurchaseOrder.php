<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * Зваказы совместных закупок.
 *
 * @ORM\Entity(repositoryClass="App\Repository\JointPurchaseOrderRepository")
 * @ORM\Table(name="joint_purchases_orders",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *      }, uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"joint_purchase_id", "user_id"}),
 *      }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class JointPurchaseOrder
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\Comment;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $payment;

    /**
     * Стоимость доставки
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $shipping_cost;

    /**
     * @var JointPurchase
     *
     * @ORM\ManyToOne(targetEntity="JointPurchase", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $joint_purchase;

    /**
     * @var JointPurchase[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="JointPurchaseOrderLine", mappedBy="order")
     */
    protected $lines;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * JointPurchaseOrder constructor.
     */
    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->lines        = new ArrayCollection();
        $this->quantity     = 1;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->joint_purchase->getTitle();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdated()
    {
        $this->updated_at = new \DateTime();
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

    /**
     * @return int|null
     */
    public function getPayment(): ?int
    {
        return $this->payment;
    }

    /**
     * @param int|null $payment
     *
     * @return $this
     */
    public function setPayment(?int $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getShippingCost(): ?int
    {
        return $this->shipping_cost;
    }

    /**
     * @param int|null $shipping_cost
     *
     * @return $this
     */
    public function setShippingCost(?int $shipping_cost): self
    {
        $this->shipping_cost = $shipping_cost;

        return $this;
    }

    /**
     * @return JointPurchase
     */
    public function getJointPurchase(): JointPurchase
    {
        return $this->joint_purchase;
    }

    /**
     * @param JointPurchase $joint_purchase
     *
     * @return $this
     */
    public function setJointPurchase(JointPurchase $joint_purchase): self
    {
        $this->joint_purchase = $joint_purchase;

        return $this;
    }

    /**
     * @return JointPurchase[]|ArrayCollection
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @param JointPurchase[]|ArrayCollection $lines
     *
     * @return $this
     */
    public function setLines($lines): self
    {
        $this->lines = $lines;

        return $this;
    }
}
