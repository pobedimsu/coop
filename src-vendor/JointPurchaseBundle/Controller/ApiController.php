<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Controller;

use Coop\JointPurchaseBundle\Entity\JointPurchaseOrder;
use Coop\JointPurchaseBundle\Entity\JointPurchaseOrderLine;
use Coop\JointPurchaseBundle\Entity\JointPurchaseProduct;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/order.update", name="jp_api_order_update", methods={"POST"})
     */
    public function orderUpdate(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if (empty($this->getUser())) {
            $data = [
                'status' => 'error',
                'message' => 'Для заказа нужно войти на сайт',
            ];

            return new JsonResponse($data);
        }

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
     * @Route("/api/admin/order.update", name="jp_api_admin_order_update", methods={"POST"})
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
}
