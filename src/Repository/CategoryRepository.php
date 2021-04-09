<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class CategoryRepository extends ClosureTreeRepository implements ServiceEntityRepositoryInterface
{
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        $manager = $registry->getManagerForClass(Category::class);

        parent::__construct($manager, $manager->getClassMetadata(Category::class));
    }

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
            'childSorts' => [
                ['field' => 'position', 'dir' => 'asc'],
                ['field' => 'title', 'dir' => 'asc']
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

    /**
     * Перегрузка метода, для работы с PostgreSQL
     *
     * А также сортировка по нескольким полям
     *
     * {@inheritdoc}
     */
    public function getNodesHierarchyQueryBuilder($node = null, $direct = false, array $options = array(), $includeNode = false)
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $idField = $meta->getSingleIdentifierFieldName();
        $subQuery = '';
        $hasLevelProp = isset($config['level']) && $config['level'];

        if (!$hasLevelProp) {
            $subQuery = ', (SELECT MAX(c2.depth) + 1 FROM '.$config['closure'];
            $subQuery .= ' c2 WHERE c2.descendant = c.descendant GROUP BY c2.descendant) AS '.self::SUBQUERY_LEVEL;
        }

        $q = $this->_em->createQueryBuilder()
            ->select('c, node, p.'.$idField.' AS parent_id'.$subQuery)
            ->from($config['closure'], 'c')
            ->innerJoin('c.descendant', 'node')
            ->leftJoin('node.parent', 'p')
            ->addOrderBy(($hasLevelProp ? 'node.'.$config['level'] : self::SUBQUERY_LEVEL), 'asc');

        if ($node !== null) {
            $q->where('c.ancestor = :node');
            $q->setParameters(compact('node'));
        } else {
            $q->groupBy('c.descendant');

            // для работы с PostgreSQL
            if ($this->_em->getConnection()->getDatabasePlatform()->getName() == 'postgresql') {
                $q->andWhere('c.depth = :cDepth');  // Restrict to 0 depth so we don't get duplicate nodes in our tree.
                $q->setParameter('cDepth', 0);
                $q->addGroupBy('c.id');
                $q->addGroupBy('p.id');
                $q->addGroupBy('node.id');
                $q->addGroupBy('node.level');
            }
        }

        if (!$includeNode) {
            $q->andWhere('c.ancestor != c.descendant');
        }

        $defaultOptions = array();
        $options = array_merge($defaultOptions, $options);

        // сортировка по нескольким полям
        if (isset($options['childSorts'])) {
            foreach ($options['childSorts'] as $sort) {
                $q->addOrderBy(
                    'node.'.$sort['field'],
                    strtolower($sort['dir']) == 'asc' ? 'asc' : 'desc'
                );
            }
        }

        if (isset($options['childSort']) && is_array($options['childSort']) &&
            isset($options['childSort']['field']) && isset($options['childSort']['dir'])
        ) {
            $q->addOrderBy(
                'node.'.$options['childSort']['field'],
                strtolower($options['childSort']['dir']) == 'asc' ? 'asc' : 'desc'
            );
        }

        return $q;
    }
}
