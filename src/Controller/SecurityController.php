<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller used to manage the application security.
 * See https://symfony.com/doc/current/cookbook/security/form_login_setup.html.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/reset/password/{token}", name="reset_password")
     */
    public function resetPassword($token, UserRepository $ur, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        $user = $ur->findOneBy(['confirmation_token' => $token]);

        if ($user === null) {
            return new Response('Токен не действителен');
        }

        if ($user->getPasswordRequestedAt() < (new \DateTime('-1 hour'))) {
            return new Response('Токен просрочен');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            // @todo счётчик кол-ва попыток
            $username = $request->request->get('_username');
            $code = $request->request->get('_code');
            $password = $request->request->get('_password');
            $password2 = $request->request->get('_password2');

            if ($username !== $user->getUsername()) {
                $errors[] = 'Неверно указан логин';
            }

            if ((int) $code !== $user->getResetPasswordCode()) {
                $errors[] = 'Неверно указан код';
            }

            if ($password !== $password2) {
                $errors[] = 'Пароли не совпадают';
            }

            if (strlen($password) < 6) {
                $errors[] = 'Пароль должен быть больше 5 символов';
            }

            if (empty($errors)) {
                $user
                    ->setPassword($encoder->encodePassword($user, $password))
                    ->setResetPasswordCode(null)
                    ->setConfirmationToken(null)
                ;

                $em->flush();

                $this->addFlash('success', 'Пароль изменён. Теперь можете войти на сайт');

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'errors' => $errors,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $helper): Response
    {
        return $this->render('security/login.html.twig', [
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     *
     * @Route("/logout", name="security_logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
