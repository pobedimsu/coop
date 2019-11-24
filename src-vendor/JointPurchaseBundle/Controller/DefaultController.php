<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Controller;

use Coop\JointPurchaseBundle\Entity\JointPurchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="jp")
     */
    public function index(EntityManagerInterface $em)
    {
        $jps = $em->getRepository(JointPurchase::class)->findBy(['status' => JointPurchase::STATUS_OPEN], ['created_at' => 'DESC']);

        return $this->render('@JointPurchase/default/index.html.twig', [
            'jps' => $jps,
        ]);
    }
}
