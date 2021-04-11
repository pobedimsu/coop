<?php

namespace SmartCore\Bundle\TexterBundle\Controller;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use SmartCore\Bundle\TexterBundle\Form\Type\TexterCreateFormType;
use SmartCore\Bundle\TexterBundle\Form\Type\TexterEditFormType;
use SmartCore\Bundle\TexterBundle\Manager\TexterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(TexterManager $texterManager, Request $request)
    {
        $pagerfanta = new Pagerfanta(new QueryAdapter($texterManager->getFindAllQuery()));
        $pagerfanta->setMaxPerPage($texterManager->getItemsCountPerPage());

        try {
            $pagerfanta->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            return $this->redirectToRoute('smart_texter_admin_index');
        }

        return $this->render('@SmartTexter/Admin/list.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createAction(TexterManager $texterManager, Request $request)
    {
        $text = $texterManager->factoryText();

        $form = $this->createForm(TexterCreateFormType::class, $text);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('smart_texter_admin_index');
            }

            if ($form->isValid()) {
                $texterManager->persist($form->getData());

                return $this->redirectToRoute('smart_texter_admin_index');
            }
        }

        return $this->render('@SmartTexter/Admin/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(TexterManager $texterManager, Request $request, $id)
    {
        $text = $texterManager->get($id);

        if (empty($text)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TexterEditFormType::class, $text);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('smart_texter_admin_index');
            }

            if ($form->isValid()) {
                $texterManager->persist($form->getData());

                return $this->redirectToRoute('smart_texter_admin_index');
            }
        }

        return $this->render('@SmartTexter/Admin/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
