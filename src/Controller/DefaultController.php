<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\MediaBundle\Entity\File;
use SmartCore\Bundle\MediaBundle\Provider\LocalProvider;
use SmartCore\Bundle\MediaBundle\Service\MediaCloudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(): Response
    {
        return $this->render('default/homepage.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/img/{filter}/{id}", name="image")
     */
    public function image(string $filter, string $id, MediaCloudService $mcs, EntityManagerInterface $em, KernelInterface $kernel): Response
    {
        if (!empty($id)) {
            try {
                $file = $em->getRepository(File::class)->find($id, 0);
            } catch (ConversionException $e) {
                // dummy
            }
        }

        if (empty($file)) {
            $path = $kernel->getProjectDir().'/public/assets/image-not-found-png-6-300x200.png';

            $file_content = file_get_contents($path);

            $format = 'png';
        } else {
            /** @var LocalProvider $provider */
            $provider = $mcs->getCollection($file->getCollection())->getProvider();

            $path = $provider->getFileTransformedPath($file, $filter);

            if (file_exists($path)) {
                $file_content = file_get_contents($path);
            } else {
                $file_content = $provider->generateTransformedFile($id, $filter);
            }

            if ($file->isMimeType('png')) {
                $format = 'png';
            } else {
                $format = 'jpeg';
            }
        }

        $response = new Response($file_content);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true'); // Параметр разрешающий кеширование на стороне браузера
        $response->headers->set('Content-Type', 'image/'.$format);
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 1186400));
        $response->headers->set('Cache-Control', 'max-age=86400, must-revalidate, public');
        $response->headers->set('Pragma', 'public');
        $response->setETag(md5($path));
        $response->setPublic();

        return $response;
    }

    /**
     * @Route("/users/", name="users")
     */
    public function users(EntityManagerInterface $em): Response
    {
        return $this->render('default/users.html.twig', [
            'users' => $em->getRepository(User::class)->findBy([], ['created_at' => 'desc']),
        ]);
    }

    /**
     * @todo вынести в UserController, а текущий UserController переименовать в ProfileController
     *
     * @Route("/user/{id}", name="user_show")
     */
    public function userShow(User $user, EntityManagerInterface $em): Response
    {
        return $this->render('default/user_show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/manual/", name="manual_index") // Так надо, потому что маршрут со slug, генерируется без завершающего слеша :(
     * @Route("/manual/{slug<.+>}", name="manual")
     */
    public function manual(string $slug = '', KernelInterface $kernel): Response
    {
        $fileMd = $kernel->getProjectDir() . '/doc/manual/' . $slug;

        $md = new \Parsedown();
        if (file_exists($fileMd) and !empty($slug)) {
            $content = $md->text(file_get_contents($fileMd));
        } else {
            $content = $md->text(file_get_contents($kernel->getProjectDir() . '/doc/manual/README.md'));
        }

        return $this->render('default/content.html.twig', [
            'content' => $content,
        ]);
    }
}
