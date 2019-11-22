<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserInviteFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InviteController extends AbstractController
{
    /**
     * @Route("/invite/", name="invite")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em): Response
    {
        $user = new User();
        $user->setInvitedByUser($this->getUser());

        $form = $this->createForm(UserInviteFormType::class, $user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

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
