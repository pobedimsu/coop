<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Позиции товаров в совместной закупке.
 *
 * @ORM\Entity()
 * @ORM\Table(name="joint_purchases_products",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"title"}),
 *      }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class JointPurchaseProduct
{
    use ColumnTrait\Uuid;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\Description;

    /**
     * ИД файла в медиалибе
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    protected $image_id;

    /**
     * Необходимое минимальное кол-во
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     */
    protected $min_quantity;

    /**
     * Цена за еденицу товара
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     * @Assert\Range(min=1, minMessage="Цена должна быть больше 0")
     */
    protected $price;

    /**
     * @var JointPurchase
     *
     * @ORM\ManyToOne(targetEntity="JointPurchase", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $joint_purchase;

    /**
     * @var JointPurchaseOrderLine[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="JointPurchaseOrderLine", mappedBy="product", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created_at" = "DESC"})
     */
    protected $order_lines;

    /**
     * JointPurchaseProduct constructor.
     */
    public function __construct(?JointPurchase $joint_purchase = null)
    {
        $this->created_at   = new \DateTime();
        $this->min_quantity = 10;
        $this->order_lines  = new ArrayCollection();
        $this->price        = 1;

        if ($joint_purchase) {
            $this->joint_purchase = $joint_purchase;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle();
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
     *
     * @deprecated
     */
    public function getCurrentQuantity(): int
    {
        $qty = 0;

        foreach ($this->order_lines as $orderLine) {
            $qty += $orderLine->getQuantity();
        }

        return $qty;
    }

    /**
     * @return string|null
     */
    public function getImageId(): ?string
    {
        return $this->image_id;
    }

    /**
     * @param string|null $image_id
     *
     * @return $this
     */
    public function setImageId(?string $image_id): self
    {
        $this->image_id = $image_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinQuantity(): int
    {
        return $this->min_quantity;
    }

    /**
     * @param int $min_quantity
     *
     * @return $this
     */
    public function setMinQuantity(int $min_quantity): self
    {
        $this->min_quantity = $min_quantity;

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
     * @return JointPurchaseOrderLine[]|ArrayCollection
     */
    public function getOrderLines()
    {
        return $this->order_lines;
    }

    /**
     * @param JointPurchaseOrderLine[]|ArrayCollection $order_lines
     *
     * @return $this
     */
    public function setOrderLines($order_lines): self
    {
        $this->order_lines = $order_lines;

        return $this;
    }
}
