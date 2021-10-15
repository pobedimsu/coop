<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Invite;
use App\Entity\User;
use App\Event\InviteEvent;
use App\Form\Type\UserInviteFormType;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/invite")
 */
class InviteController extends AbstractController
{
    /**
     * @Route("/", name="invite")
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
                $encodedPassword = $encoder->encodePassword($user, $user->getPlainPassword());
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

    /**
     * @Route("/generate/", name="invite_generate")
     */
    public function generate(EntityManagerInterface $em): Response
    {
        $invite = $em->getRepository(Invite::class)->findActiveByUser($this->getUser());

        if ($invite === null) {
            $invite = new Invite($this->getUser());

            $em->persist($invite);
            $em->flush();
        }

        return $this->render('invite/generate.html.twig', [
            'invite' => $invite,
        ]);
    }

    /**
     * @Route("/register/{id}", name="invite_register")
     */
    public function register(
        $id,
        Request $request,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em,
        $isUserForm,
        AuthenticationManagerInterface $authenticationManager,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $dispatcher
    ): Response {
        // Не существующий инвайт
        try {
            $invite = $em->getRepository(Invite::class)->findOneBy([
                'id' => Uuid::fromString($id),
                'is_used' => false,
            ]);
        } catch (InvalidUuidStringException $e) {
            return $this->redirectToRoute('security_login');
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('offers');
        }

        // Просрочено или не найдено приглашение
        if (empty($invite) or !($invite->getCreatedAt() > new \DateTime('-1 day'))) {
            return new Response('Приглашение не действительно. <a href="/">Перейти на главную</a>'); // @todo tpl
        }

        $user = new User();
        $user
            ->setInvitedByUser($invite->getUser())
            ->setInvite($invite)
        ;

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
                $encodedPassword = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encodedPassword);

                $em->persist($user);
                $em->flush();

                $token = $authenticationManager->authenticate(
                    new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles())
                );

                $tokenStorage->setToken($token);

                $this->addFlash('success', 'Регистрация прошла успено.');

                $dispatcher->dispatch($user->getInvite(), InviteEvent::REGISTER);

                return $this->redirectToRoute('homepage');
            }
        }

        if (!$request->getSession()->get('accepted')) {
            return $this->render('invite/register_welcome.html.twig', ['invite' => $invite,]);
        }

        return $this->render('invite/register.html.twig', [
            'invite' => $invite,
            'form' => $form->createView(),
        ]);
    }
}
