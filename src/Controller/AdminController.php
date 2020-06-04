<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Form\Type\CategoryFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(UserRepository $ur): Response
    {
        $ur->rebuildClosure(); // @todo remove

        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/category/", name="admin_category")
     */
    public function categoryIndex(Request $request, EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findBy([], ['position' => 'ASC', 'title' => 'ASC']);

        $form = $this->createForm(CategoryFormType::class);
        $form->remove('update');
        $form->remove('cancel');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->has('cancel') and $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('admin_category');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Категория создана');

                return $this->redirectToRoute('admin_category');
            }
        }

        $options = [
            'decorate' => true,
            'rootOpen' => '<ol>',
            'rootClose' => '</ol>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'childSorts' => [
                ['field' => 'position', 'dir' => 'asc'],
                ['field' => 'title', 'dir' => 'asc']
            ],
            'nodeDecorator' => function($node) {
                $path = $this->generateUrl('admin_category_edit', ['id' => $node['id']]);

                return '<a href="'.$path.'">'.$node['title'].'</a> ('.$node['name'].') pos: '.$node['position'];
            }
        ];

        $htmlTree = $em->getRepository(Category::class)->childrenHierarchy(null,false, $options);

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'html_tree' => $htmlTree,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/{id}/", name="admin_category_edit")
     */
    public function categoryEdit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->remove('create');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('admin_category');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($category);
                $em->flush();

                $this->addFlash('success', 'Категория обновлена');

                return $this->redirectToRoute('admin_category');
            }
        }

        return $this->render('admin/category/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
