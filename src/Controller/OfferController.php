<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Offer;
use App\Form\Type\OfferFormType;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\MediaBundle\Service\MediaCloudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/offers")
 */
class OfferController extends AbstractController
{
    /**
     * @Route("/", name="offers")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $offers = $em->getRepository(Offer::class)
            ->getFindQueryBuilder([
                'category' => $request->query->get('category'),
                'search' => $request->query->get('search'),
            ])
            ->getQuery()
            ->getResult()
        ;

        return $this->render('offer/index.html.twig', [
            'categories' => $em->getRepository(Category::class)->childrenHierarchyList(),
            'offers' => $offers,
        ]);
    }

    /**
     * @Route("/create/", name="offer_create")
     */
    public function create(Request $request, EntityManagerInterface $em, MediaCloudService $mc): Response
    {
        $offer = new Offer();
        $offer->setUser($this->getUser());

        $form = $this->createForm(OfferFormType::class, $offer);
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('offers_my');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $image = $form['image_id']->getData();

                if ($image instanceof File) {
                    $fileId = $mc->getCollection('of')->upload($image);

                    $offer->setImageId((string) $fileId);
                }

                $em->persist($offer);
                $em->flush();

                $this->addFlash('success', 'Предложение создано.');

                return $this->redirectToRoute('offers_my');
            }
        }

        return $this->render('offer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit/", name="offer_edit")
     */
    public function edit(Offer $offer, Request $request, EntityManagerInterface $em, MediaCloudService $mc): Response
    {
        if ($offer->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('offer_show', ['id' => $offer->getId()]);
        }

        $form = $this->createForm(OfferFormType::class, $offer);
        $form->remove('create');

        $oldImage = $offer->getImageId();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('offer_show', ['id' => $offer->getId()]);
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $image = $form['image_id']->getData();

                if ($image instanceof File) {
                    $fileId = $mc->getCollection('of')->upload($image);
                    // $fileId = $mc->upload('of', $image); @todo

                    if ($oldImage) {
                        $mc->getCollection('of')->remove($oldImage);
                        // $mc->remove('of', (int) $oldImage); @todo
                    }

                    $offer->setImageId((string) $fileId);
                } elseif (isset($_POST['_delete_']['image_id'])) {
                    $mc->getCollection('of')->remove($oldImage);

                    $offer->setImageId(null);
                } else {
                    $offer->setImageId((string) $oldImage);
                }

                $em->persist($offer);
                $em->flush();

                $this->addFlash('success', 'Предложение обновлено.');

                return $this->redirectToRoute('offer_show', ['id' => $offer->getId()]);
            }
        }

        return $this->render('offer/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/my/", name="offers_my")
     */
    public function my(EntityManagerInterface $em): Response
    {
        $offers = $em->getRepository(Offer::class)->findBy(['user' => $this->getUser()]);

        return $this->render('offer/my.html.twig', [
            'offers' => $offers,
        ]);
    }

    /**
     * @Route("/{id}/", name="offer_show")
     */
    public function show(string $id, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(Offer::class)->findOneBy(['id' => $id]);

        if (empty($offer)) {
            return $this->redirectToRoute('offers');
        }

        return $this->render('offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }
}
