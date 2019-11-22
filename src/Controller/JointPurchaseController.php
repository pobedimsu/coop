<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\JointPurchase;
use App\Entity\JointPurchaseOrder;
use App\Entity\JointPurchaseOrderLine;
use App\Entity\JointPurchaseProduct;
use App\Form\Type\JointPurchaseFormType;
use App\Form\Type\JointPurchaseProductFormType;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\MediaBundle\Service\MediaCloudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/jp")
 */
class JointPurchaseController extends AbstractController
{
    /**
     * @Route("/order_update/", name="jp_order_update", methods={"POST"})
     */
    public function orderUpdate(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $product = $em->find(JointPurchaseProduct::class, $request->request->get('product'));
        } catch (ConversionException $e) {
            $data = [
                'status' => 'error',
                'message' => 'Товар не найден',
            ];

            return new JsonResponse($data);
        }

        if (empty($product)) {
            $data = [
                'status' => 'error',
                'message' => 'Товар не найден',
            ];
        } else {
            $comment  = $request->request->get('comment');
            $quantity = (int) $request->request->get('quantity');

            $orderLine = $em->getRepository(JointPurchaseOrderLine::class)->findOneBy(['product' => $product]);

            if (empty($orderLine)) {
                $order = $em->getRepository(JointPurchaseOrder::class)->findOneBy(['joint_purchase' => $product->getJointPurchase(), 'user' => $this->getUser()]);

                if (empty($order)) {
                    $order = new JointPurchaseOrder();
                    $order
                        ->setUser($this->getUser())
                        ->setJointPurchase($product->getJointPurchase())
                    ;
                    $em->persist($order);
                    $em->flush();
                }

                if ($quantity > 0) {
                    $orderLine = new JointPurchaseOrderLine();
                    $orderLine
                        ->setOrder($order)
                        ->setProduct($product)
                        ->setQuantity($quantity)
                        ->setComment($comment)
                        ->setPrice($product->getPrice())
                    ;
                    $em->persist($orderLine);
                    $em->flush();

                    $this->addFlash('success', 'Заявка добавлена'); // @todo remove
                } else {
                    $this->addFlash('notice', 'Необходимо указать кол-во больше 0'); // @todo remove
                }
            } else { // $orderLine существует
                if ($orderLine->getOrder()->getUser() != $this->getUser()) {
                    $data = [
                        'status' => 'error',
                        'message' => 'Доступ запрещён',
                    ];

                    return new JsonResponse($data);
                }

                if ($quantity > 0) {
                    $orderLine
                        ->setQuantity($quantity)
                        ->setComment($comment)
                    ;
                    $em->persist($orderLine);
                    $em->flush();

                    $this->addFlash('success', 'Заявка обновлена'); // @todo remove
                } else {
                    /** @var JointPurchaseOrder $order */
                    $order = $orderLine->getOrder();

                    $em->remove($orderLine);
                    $em->flush();

                    if ($order->getLines()->count() == 0) {
                        $em->remove($order);
                        $em->flush();
                    }

                    $this->addFlash('notice', 'Заявка удалена'); // @todo remove
                }
            }

            $data = [
                'status' => 'success',
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/admin_order_update/", name="jp_admin_order_update", methods={"POST"})
     */
    public function adminOrderUpdate(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $order = $em->find(JointPurchaseOrder::class, $request->request->get('order_id'));
        } catch (ConversionException $e) {
            $data = [
                'status' => 'error',
                'message' => 'Заказ не найден',
            ];

            return new JsonResponse($data);
        }

        if (empty($order)) {
            $data = [
                'status' => 'error',
                'message' => 'Заказ не найден',
            ];
        } else {
            if ($order->getJointPurchase()->getOrganizer() != $this->getUser()) {
                $data = [
                    'status' => 'error',
                    'message' => 'Доступ запрещён',
                ];

                return new JsonResponse($data);
            }

            $comment        = $request->request->get('comment');
            $payment        = (int) $request->request->get('payment');
            $shipping_cost  = (int) $request->request->get('shipping_cost');

            if ($payment <= 0) {
                $payment = null;
            }

            if ($shipping_cost <= 0) {
                $shipping_cost = null;
            }

            $order
                ->setComment($comment)
                ->setPayment($payment)
                ->setShippingCost($shipping_cost)
            ;
            $em->flush();

            $this->addFlash('success', 'Заказ обновлён'); // @todo remove

            $data = [
                'status' => 'success',
                'message' => 'Заказ обновлён',
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/", name="jp")
     */
    public function index(EntityManagerInterface $em)
    {
        $jps = $em->getRepository(JointPurchase::class)->findBy(['status' => JointPurchase::STATUS_OPEN], ['created_at' => 'DESC']);

        return $this->render('joint_purchase/index.html.twig', [
            'jps' => $jps,
        ]);
    }

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

                return $this->redirectToRoute('jp_create_product', ['id' => $jp->getId()]);
            }
        }

        return $this->render('joint_purchase/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/my/", name="jp_my")
     */
    public function my(EntityManagerInterface $em): Response
    {
        $jps = $em->getRepository(JointPurchase::class)->findBy(['organizer' => $this->getUser()], ['created_at' => 'DESC']);

        return $this->render('joint_purchase/my.html.twig', [
            'jps' => $jps,
        ]);
    }

    /**
     * @Route("/my_orders/", name="jp_my_orders")
     */
    public function myOrders(EntityManagerInterface $em): Response
    {
        $orders = $em->getRepository(JointPurchaseOrder::class)->findBy(['user' => $this->getUser()], ['created_at' => 'DESC']);

        return $this->render('joint_purchase/my_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/{id}/edit/products/", name="jp_edit_products")
     */
    public function editProducts(JointPurchase $jp): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('joint_purchase/edit_products.html.twig', [
            'jp' => $jp,
        ]);
    }

    /**
     * @Route("/{id}/edit/product/", name="jp_edit_product")
     */
    public function editProduct(JointPurchaseProduct $product, Request $request, EntityManagerInterface $em, MediaCloudService $mc): Response
    {
        $jp = $product->getJointPurchase();

        if ($jp->getOrganizer() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(JointPurchaseProductFormType::class, $product);
        $form->remove('create');

        $oldImage = $product->getImageId();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('jp_edit_products', ['id' => $jp->getId()]);
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $image = $form['image_id']->getData();

                if ($image instanceof File) {
                    $fileId = $mc->getCollection('jp')->upload($image);
                    // $fileId = $mc->upload('of', $image); @todo

                    if ($oldImage) {
                        $mc->getCollection('jp')->remove((int) $oldImage);
                        // $mc->remove('of', (int) $oldImage); @todo
                    }

                    $product->setImageId((string) $fileId);
                } elseif (isset($_POST['_delete_']['image_id'])) {
                    $mc->getCollection('jp')->remove((int) $oldImage);

                    $product->setImageId(null);
                } else {
                    $product->setImageId((string) $oldImage);
                }

                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Товар обновлён');

                return $this->redirectToRoute('jp_edit_products', ['id' => $jp->getId()]);
            }
        }

        return $this->render('joint_purchase/edit_product.html.twig', [
            'form' => $form->createView(),
            'jp' => $jp,
        ]);
    }

    /**
     * @Route("/{id}/create_product/", name="jp_create_product")
     */
    public function createProduct(JointPurchase $jp, Request $request, EntityManagerInterface $em, MediaCloudService $mc): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(JointPurchaseProductFormType::class, new JointPurchaseProduct($jp));
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('jp_edit_products', ['id' => $jp->getId()]);
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $image = $form['image_id']->getData();

                if ($image instanceof File) {
                    $fileId = $mc->getCollection('jp')->upload($image);

                    $form->getData()->setImageId((string) $fileId);
                }

                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Товар добавлен');

                return $this->redirectToRoute('jp_edit_products', ['id' => $jp->getId()]);
            }
        }

        return $this->render('joint_purchase/create_product.html.twig', [
            'form' => $form->createView(),
            'jp' => $jp,
        ]);
    }

    /**
     * @Route("/{id}/edit/", name="jp_edit")
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

        return $this->render('joint_purchase/edit.html.twig', [
            'form' => $form->createView(),
            'jp' => $jp,
        ]);
    }

    /**
     * @Route("/{id}/orders/", name="jp_orders")
     */
    public function orders(JointPurchase $jp, Request $request, EntityManagerInterface $em): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            return $this->redirectToRoute('jp');
        }

        return $this->render('joint_purchase/orders.html.twig', [
            'jp' => $jp,
            'orders' => $jp->getOrders(),
        ]);
    }

    /**
     * @Route("/{id}/", name="jp_show")
     */
    public function show(JointPurchase $jp, Request $request): Response
    {
        return $this->render('joint_purchase/show.html.twig', [
            'jp' => $jp,
        ]);
    }
}
