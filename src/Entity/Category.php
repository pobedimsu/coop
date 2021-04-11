<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\Table(name="categories",
 *      indexes={
 *          @ORM\Index(columns={"created_at"}),
 *          @ORM\Index(columns={"level"}),
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
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\Description;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Position;

    /**
     * @ORM\Column(type="string", length=190, unique=true, nullable=true)
     * @Gedmo\Slug(fields={"title"})
     * Assert\NotBlank()
     */
    protected ?string $name;

    /**
     * This parameter is optional for the closure strategy
     *
     * @ORM\Column(name="level", type="integer", nullable=false, options={"default":1})
     * @Gedmo\TreeLevel
     */
    protected int $level;

    /**
     * @Gedmo\TreeParent
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     */
    protected ?Category $parent;

    /**
     * @var Category[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    protected Collection $children;

    /**
     * Для отображения в формах. Не маппится в БД.
     */
    protected string $form_title = '';

    public function __construct()
    {
        $this->children   = new ArrayCollection();
        $this->created_at = new \DateTime();
        $this->level      = 1;
        $this->position   = 0;
    }

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
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Category[]|Collection $children
     */
    public function setChildren(Collection $children): self
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
