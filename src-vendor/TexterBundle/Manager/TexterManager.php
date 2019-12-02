<?php

namespace SmartCore\Bundle\TexterBundle\Manager;

use SmartCore\Bundle\TexterBundle\Entity\Text;
use SmartCore\Bundle\TexterBundle\Model\TextModel;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TexterManager
{
    use ContainerAwareTrait;

    /** @var \Doctrine\ORM\EntityManager $em */
    protected $em;

    /** @var \SmartCore\Bundle\TexterBundle\Repository\TextRepository */
    protected $textersRepo;

    /** @var integer */
    protected $itemsPerPage;

    /**
     * TexterManager constructor.
     *
     * @param ContainerInterface $container
     * @param int                $itemsPerPage
     */
    public function __construct(ContainerInterface $container, $itemsPerPage = 10)
    {
        $this->container = $container;
        $this->em        = $container->get('doctrine.orm.entity_manager');

        $this->textersRepo = $this->em->getRepository('SmartTexterBundle:Text');
        $this->setItemsCountPerPage($itemsPerPage);
    }

    /**
     * @return int
     */
    public function getItemsCountPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function setItemsCountPerPage($count)
    {
        $this->itemsPerPage = $count;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return TextModel|null
     */
    public function get($id)
    {
        return $this->textersRepo->find($id);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getFindAllQuery()
    {
        return $this->textersRepo->getFindAllQuery();
    }

    /**
     * @return TextModel
     */
    public function factoryText()
    {
        return new Text();
    }

    /**
     * @param TextModel $text
     *
     * @return $this
     */
    public function persist(TextModel $text)
    {
        $this->em->persist($text);
        $this->em->flush($text);

        $textName = $text->getName();
        if (null === $textName or is_numeric($textName)) {
            $text->setName($text->getId());
            $this->em->flush($text);
        }

        return $this;
    }
}
