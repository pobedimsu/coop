<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Controller;

use Coop\JointPurchaseBundle\Entity\JointPurchase;
use Coop\JointPurchaseBundle\Entity\JointPurchaseOrder;
use Coop\JointPurchaseBundle\Entity\JointPurchaseOrderLine;
use Coop\JointPurchaseBundle\Entity\JointPurchaseProduct;
use Coop\JointPurchaseBundle\Form\Type\JointPurchaseFormType;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/orders/my/", name="jp_my_orders")
     */
    public function my(EntityManagerInterface $em): Response
    {
        $orders = $em->getRepository(JointPurchaseOrder::class)->findBy(['user' => $this->getUser()], ['created_at' => 'DESC']);

        return $this->render('@JointPurchase/order/my.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/order/{id}/edit/", name="jp_edit")
     */
    public function edit(JointPurchase $jp, Request $request, EntityManagerInterface $em): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->query->get('status') == 'open') {
            if ($jp->getStatus() == JointPurchase::STATUS_DRAFT) {
                $jp->setStatus(JointPurchase::STATUS_OPEN);
                $em->persist($jp);
                $em->flush();
            }

            $this->addFlash('success', 'Совместная закупка открыта для приёма заявок');

            return $this->redirectToRoute('jp_edit', ['id' => $jp->getId()]);
        }

        $form = $this->createForm(JointPurchaseFormType::class, $jp);
        $form->remove('create');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('jp');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($jp);
                $em->flush();

                $this->addFlash('success', 'Совместная закупка обновлена');

                return $this->redirectToRoute('jp_edit', ['id' => $jp->getId()]);
            }
        }

        return $this->render('@JointPurchase/order/edit.html.twig', [
            'form' => $form->createView(),
            'jp' => $jp,
        ]);
    }
}
