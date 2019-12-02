<?php

namespace SmartCore\Bundle\TexterBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use SmartCore\Bundle\TexterBundle\Form\Type\TexterCreateFormType;
use SmartCore\Bundle\TexterBundle\Form\Type\TexterEditFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $texterManager = $this->get('smart_core.texter.manager');

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($texterManager->getFindAllQuery()));
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
    public function createAction(Request $request)
    {
        $text = $this->get('smart_core.texter.manager')->factoryText();

        $form = $this->createForm(TexterCreateFormType::class, $text);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('smart_texter_admin_index');
            }

            if ($form->isValid()) {
                $this->get('smart_core.texter.manager')->persist($form->getData());

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
    public function editAction(Request $request, $id)
    {
        $text = $this->get('smart_core.texter.manager')->get($id);

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
                $this->get('smart_core.texter.manager')->persist($form->getData());

                return $this->redirectToRoute('smart_texter_admin_index');
            }
        }

        return $this->render('@SmartTexter/Admin/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
