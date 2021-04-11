<?php

declare(strict_types=1);

namespace App\Form\Tree;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

class CategoryLoader implements EntityLoaderInterface
{
    /** @var \Doctrine\ORM\EntityRepository */
    private ObjectRepository $repo;
    protected array $result;
    protected int $level;
    protected bool $only_active = false;

    public function __construct(ObjectManager $em, $manager = null, $class = null)
    {
        $this->repo = $em->getRepository($class);
    }

    public function setOnlyActive(bool $only_active): self
    {
        $this->only_active = $only_active;

        return $this;
    }

    /**
     * Returns an array of entities that are valid choices in the corresponding choice list.
     *
     * @return Category[]
     */
    public function getEntities(): array
    {
        $this->result = [];
        $this->level = 0;

        $this->addChild();

        return $this->result;
    }

    protected function addChild(?Category $parent_folder = null): void
    {
        $level = $this->level;
        $ident = '';
        while ($level--) {
//            $ident .= '&nbsp;&nbsp;';
            $ident .= '⋅⋅ ';
        }

        $this->level++;

        $criteria = ['parent' => $parent_folder];

        if ($this->only_active) {
            $criteria['is_active'] = true;
        }

        $folders = $this->repo->findBy($criteria, ['position' => 'ASC', 'title' => 'ASC']);

        /** @var $folder Category */
        foreach ($folders as $folder) {
            $folder->setFormTitle($ident.$folder->getTitle());
            $this->result[] = $folder;
            $this->addChild($folder);
        }

        $this->level--;
    }

    /**
     * Returns an array of entities matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array $values The values of the identifiers.
     *
     * @return array The entities.
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        if (isset($values[0]) and empty($values[0])) {
            return [];
        }

        return $this->repo->findBy(
            [$identifier => $values]
        );
    }
}
