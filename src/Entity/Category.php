<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\Table(name="categories",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"position"}),
 *          @ORM\Index(columns={"title"}),
 *      },
 * )
 * @Gedmo\Tree(type="closure")
 * @Gedmo\TreeClosure(class="CategoryClosure")
 */
class Category
{
    use ColumnTrait\Id;
    use ColumnTrait\NameUnique;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\Description;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Position;

    /**
     * This parameter is optional for the closure strategy
     *
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=false, options={"default":1})
     * @Gedmo\TreeLevel
     */
    protected $level;

    /**
     * @var Category|null
     *
     * @Gedmo\TreeParent
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     */
    protected $parent;

    /**
     * @var Category[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    protected $children;

    /**
     * Для отображения в формах. Не маппится в БД.
     */
    protected $form_title = '';

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->level      = 1;
        $this->position   = 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Category[]|Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Category[]|Collection $children
     *
     * @return $this
     */
    public function setChildren($children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getFormTitle(): string
    {
        return $this->form_title;
    }

    public function setFormTitle(string $form_title): self
    {
        $this->form_title = $form_title;

        return $this;
    }
}
