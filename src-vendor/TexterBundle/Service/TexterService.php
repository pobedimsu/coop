<?php

namespace SmartCore\Bundle\TexterBundle\Service;

use Doctrine\ORM\EntityManager;
use SmartCore\Bundle\TexterBundle\Entity\Text;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

class TexterService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \SmartCore\Bundle\TexterBundle\Repository\TextRepository
     */
    protected $tetxersRepo;

    /**
     * @var integer
     */
    protected $itemsPerPage;

    /**
     * @param EntityManager $em
     * @param integer $itemsPerPage
     */
    public function __construct(EntityManager $em, $itemsPerPage = 10)
    {
        $this->em               = $em;
        $this->textersRepo      = $em->getRepository('SmartTexterBundle:Text');

        $this->setItemsCountPerPage($itemsPerPage);
    }

    /**
     * @return integer
     */
    public function getItemsCountPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param integer $count
     * @return $this
     */
    public function setItemsCountPerPage($count)
    {
        $this->itemsPerPage = $count;

        return $this;
    }

    /**
     * @param int $id
     * @return Text|null
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
}
