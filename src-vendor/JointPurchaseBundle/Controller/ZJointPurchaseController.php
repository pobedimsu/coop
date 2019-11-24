<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Controller;

use Coop\JointPurchaseBundle\Entity\JointPurchase;
use Coop\JointPurchaseBundle\Form\Type\JointPurchaseFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Класс называется с буквы Z, для того, чтобы его маршруты были последними по списку.
 */
class ZJointPurchaseController extends AbstractController
{
    /**
     * @Route("/create/", name="jp_create")
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $jp = new JointPurchase();
        $jp->setOrganizer($this->getUser());

        $form = $this->createForm(JointPurchaseFormType::class, $jp);
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('jp_my');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($jp);
                $em->flush();

                $this->addFlash('success', 'Совместная закупка создана');

                return $this->redirectToRoute('jp_product_create', ['jp' => $jp->getId()]);
            }
        }

        return $this->render('@JointPurchase/joint_purchase/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/my/", name="jp_my")
     */
    public function my(EntityManagerInterface $em): Response
    {
        $jps = $em->getRepository(JointPurchase::class)->findBy(['organizer' => $this->getUser()], ['created_at' => 'DESC']);

        return $this->render('@JointPurchase/joint_purchase/my.html.twig', [
            'jps' => $jps,
        ]);
    }

    /**
     * @Route("/{jp}/products/", name="jp_edit_products")
     */
    public function editProducts(JointPurchase $jp): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@JointPurchase/joint_purchase/edit_products.html.twig', [
            'jp' => $jp,
        ]);
    }

    /**
     * @Route("/{jp}/orders/", name="jp_orders")
     */
    public function orders(JointPurchase $jp, Request $request, EntityManagerInterface $em): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            return $this->redirectToRoute('jp');
        }

        return $this->render('@JointPurchase/joint_purchase/orders.html.twig', [
            'jp' => $jp,
            'orders' => $jp->getOrders(),
        ]);
    }

    /**
     * @Route("/{jp}/", name="jp_show")
     */
    public function show(JointPurchase $jp, Request $request): Response
    {
        return $this->render('@JointPurchase/joint_purchase/show.html.twig', [
            'jp' => $jp,
        ]);
    }
}
