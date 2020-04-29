<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class CategoryRepository extends ClosureTreeRepository
{
    use RepositoryTrait\FindByQuery;

    /**
     * Выборка списка элементов (объектов) для постройки form select
     *
     * @param null  $node
     * @param bool  $direct
     * @param array $options
     * @param bool  $includeNode
     * @param bool  $asObject
     *
     * @return Category[]|null
     */
    public function childrenHierarchyList($node = null, $direct = false, array $options = [], $includeNode = false, bool $asObject = false): ?array
    {
        $options = [
            'childSort' => [
                'field' => 'position',
                'dir' => 'asc',
            ],
        ] + $options;

        $nestedTree = $this->childrenHierarchy($node, $direct, $options, $includeNode);

        $build = function ($tree) use (&$build, &$options, $asObject) {
            $output = [];
            foreach ($tree as $node) {
                $output[] = $asObject ? $this->find($node['id']) : $node;

                if (count($node['__children']) > 0) {
                    foreach ($build($node['__children']) as $item) {
                        $output[] = $item;
                    }
                }
            }

            return $output;
        };

        return $build($nestedTree);
    }
}
