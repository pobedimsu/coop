<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserInviteFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InviteController extends AbstractController
{
    /**
     * @Route("/invite/", name="invite")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, $isUserForm): Response
    {
        $user = new User();
        $user->setInvitedByUser($this->getUser());

        $form = $this->createForm(UserInviteFormType::class, $user);

        if (!$isUserForm) {
            $form
                ->remove('sex')
                ->remove('is_smoking')
                ->remove('is_alcohol')
                ->remove('is_meat_consumption')
            ;
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($isUserForm) {
                if ($form->get('is_smoking')->getData() === null) {
                    $form->get('is_smoking')->addError(new FormError('Значение недопустимо.'));
                }

                if ($form->get('is_alcohol')->getData() === null) {
                    $form->get('is_alcohol')->addError(new FormError('Значение недопустимо.'));
                }

                if ($form->get('is_meat_consumption')->getData() === null) {
                    $form->get('is_meat_consumption')->addError(new FormError('Значение недопустимо.'));
                }
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $encodedPassword = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($encodedPassword);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Участник <b>'.$user->getUsername().'</b> добавлен.');

                return $this->redirectToRoute('users');
            }
        }

        return $this->render('invite/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
