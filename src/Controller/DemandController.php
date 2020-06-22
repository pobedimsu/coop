<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Demand;
use App\Form\Type\DemandFormType;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demand")
 */
class DemandController extends AbstractController
{
    /**
     * @Route("/", name="demand")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter(
            $em->getRepository(Demand::class)->getFindByQuery(null, ['created_at' => 'DESC'])
        ));
        $pagerfanta->setMaxPerPage(20);

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw $this->createNotFoundException();
        }

        return $this->render('demand/index.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/create/", name="demand_create")
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $demand = new Demand();
        $demand->setUser($this->getUser());

        $form = $this->createForm(DemandFormType::class, $demand);
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('demand');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($demand);
                $em->flush();

                $this->addFlash('success', 'Заявка создана.');

                return $this->redirectToRoute('demand');
            }
        }

        return $this->render('demand/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/", name="demand_show")
     */
    public function show(Demand $demand, EntityManagerInterface $em, Request $request): Response
    {
        if ($request->query->has('remove') and $this->isGranted('ROLE_ADMIN')) {
            $em->remove($demand);
            $em->flush();

            $this->addFlash('success', 'Заявка удалена.');

            return $this->redirectToRoute('demand');
        }

        return $this->render('demand/show.html.twig', [
            'demand' => $demand,
        ]);
    }

    /**
     * @Route("/{id}/edit/", name="demand_edit")
     */
    public function edit(Demand $demand): Response
    {
        return $this->render('demand/edit.html.twig', [
            'demand' => $demand,
        ]);
    }
}
