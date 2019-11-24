<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Controller;

use Coop\JointPurchaseBundle\Entity\JointPurchase;
use Coop\JointPurchaseBundle\Entity\JointPurchaseProduct;
use Coop\JointPurchaseBundle\Form\Type\JointPurchaseProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\MediaBundle\Service\MediaCloudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/{jp}/create_product/", name="jp_product_create")
     */
    public function create(JointPurchase $jp, Request $request, EntityManagerInterface $em, MediaCloudService $mc): Response
    {
        if ($jp->getOrganizer() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(JointPurchaseProductFormType::class, new JointPurchaseProduct($jp));
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('jp_edit_products', ['jp' => $jp->getId()]);
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

                return $this->redirectToRoute('jp_edit_products', ['jp' => $jp->getId()]);
            }
        }

        return $this->render('@JointPurchase/product/create.html.twig', [
            'form' => $form->createView(),
            'jp' => $jp,
        ]);
    }

    /**
     * @Route("/product/{id}/edit/", name="jp_edit_product")
     */
    public function edit(JointPurchaseProduct $product, Request $request, EntityManagerInterface $em, MediaCloudService $mc): Response
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
                return $this->redirectToRoute('jp_edit_products', ['jp' => $jp->getId()]);
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

                return $this->redirectToRoute('jp_edit_products', ['jp' => $jp->getId()]);
            }
        }

        return $this->render('@JointPurchase/product/edit.html.twig', [
            'form' => $form->createView(),
            'jp' => $jp,
        ]);
    }
}
