<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/user/create", name="api_create_user", methods={"POST"})
     */
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $encoder): JsonResponse
    {
        $access_token = $request->request->get('access_token');
        $username     = $request->request->get('username');
        $password     = $request->request->get('password');
        $firstname    = $request->request->get('firstname');
        $lastname     = $request->request->get('lastname');

        if (empty($access_token)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'access_token not found',
            ]);
        }

        if (empty($username)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'username not found',
            ]);
        }

        if (empty($firstname)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'firstname not found',
            ]);
        }

        if (empty($lastname)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'lastname not found',
            ]);
        }

        if (!empty($password) and strlen($password) < 8) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'password min length is 8',
            ]);
        }

        if (!empty($password) and strlen($password) > 32) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'password max length is 32',
            ]);
        }

        if ($em->getRepository(User::class)->findOneBy(['username' => $username])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'username is already exist',
            ]);
        }

        $inviter = $em->getRepository(User::class)->findOneBy(['api_token' => $access_token]);
        if (empty($inviter)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Inviter user not found',
            ]);
        }

        $password = $password ? $password : $this->gneratetToken(32);

        $user = new User();
        $user
            ->setInvitedByUser($inviter)
            ->setUsername($username)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setPassword($encoder->hashPassword($user, $password))
        ;

        $em->persist($user);
        $em->flush();

        $data = [
            'status' => 'success',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'password' => $password,
            ],
        ];

        return new JsonResponse($data);
    }

    protected function gneratetToken(int $length): string
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "_-=.@#";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }

        return $token;
    }
}
