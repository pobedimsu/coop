<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserChangePasswordFormType;
use App\Form\Type\UserFormType;
use App\Repository\UserRepository;
use App\Service\TelegramService;
use App\Util\TokenGenerator;
use App\Util\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profile")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="profile")
     */
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserFormType::class, $this->getUser());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($this->getUser());
                $em->flush();

                $this->addFlash('success', 'Основные данные обновлены');

                return $this->redirectToRoute('profile');
            }
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/invited/", name="profile_invited")
     */
    public function invited(): Response
    {
        // @todo постраничность
        return $this->render('user/invited.html.twig');
    }

    /**
     * @Route("/invited/reset/password/{id}", name="profile_invited_reset_password")
     */
    public function invitedResetPassword($id, TelegramService $telegram, UserRepository $ur, TokenGenerator $tokenGenerator, EntityManagerInterface $em): Response
    {
        try {
            $user = $ur->findOneBy(['id' => Uuid::fromString($id)]);
        } catch (InvalidUuidStringException $e) {
            return $this->redirectToRoute('profile_invited');
        }

        if ($user->getInvitedByUser() !== $this->getUser()) {
            return $this->redirectToRoute('profile_invited');
        }

        if ($user->getTelegramUsername()) {
            if ($user->getPasswordRequestedAt() === null
                or $user->getConfirmationToken() === null
                or $user->getPasswordRequestedAt() < (new \DateTime('-1 hour'))
            ) {
                $user
                    ->setConfirmationToken($tokenGenerator->generateToken())
                    ->setResetPasswordCode(random_int(10000, 99999))
                    ->setPasswordRequestedAt(new \DateTime())
                ;

                $em->flush();

                $telegram->sendMessage($user,
                    'Сгенерирована ссылка для сброса пароля, возьмите её у своего поручителя. Код подтверждения: ' . $user->getResetPasswordCode()
                );
            }
        }

        return $this->render('user/invited_reset_password.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/password/", name="profile_password")
     */
    public function password(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(UserChangePasswordFormType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('update')->isClicked() and $form->isValid()) {
                /** @var User $user */
                $user = $this->getUser();

                $currentPassword = $form->get('current_password')->getData();

                if (!$encoder->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Неверно указан текущий пароль');

                    return $this->redirectToRoute('profile_password');
                }

                $validator = new UserValidator();

                try {
                    $password = $form->get('password')->getData();

                    $validator->validatePassword($password);

                    $user->setPassword(
                        $encoder->encodePassword($user, $password)
                    );

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Пароль обновлён');
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('profile_password');
            }
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/geoposition/", name="profile_geoposition")
     */
    public function geoposition(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $user
                ->setLatitude((float) $request->request->get('latitude'))
                ->setLongitude((float) $request->request->get('longitude'))
            ;

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Координаты сохранены.');

            return $this->redirectToRoute('profile_geoposition');
        }

        return $this->render('user/geoposition.html.twig', [
            'latitude'  => $user->getLatitude(),
            'longitude' => $user->getLongitude(),
        ]);
    }

    /**
     * @Route("/telegram/", name="profile_telegram")
     */
    public function telegram(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->query->has('remove')) {
            $user->setTelegramUserId(null);
            $user->setTelegramUsername(null);

            $em->flush();

            return $this->redirectToRoute('profile_telegram');
        }

        $countdown = 0;

        if ($user->getTelegramUsername()) {
            $code = null;
        } else {
            $cache = new FilesystemAdapter();

            $code = $cache->get('connect_telegram_account_user'.$user->getId()->serialize(), function (ItemInterface $item) {
                $item->expiresAfter(60 * 5);

                return random_int(10000, 99999);
            });

            // Сохранение в кеше user_id
            $user_id = $cache->get('connect_telegram_account_code'.$code, function (ItemInterface $item) use ($user) {
                $item->expiresAfter(60 * 5);

                return $user->getId()->serialize();
            });

            /** @var CacheItem $code_item */
            $code_item = $cache->getItem('connect_telegram_account_user'.$user->getId()->serialize());

            $countdown = $code_item->getMetadata()['expiry'] - time();
        }

        return $this->render('user/telegram.html.twig', [
            'code' => $code,
            'countdown' => $countdown,
        ]);
    }
}
