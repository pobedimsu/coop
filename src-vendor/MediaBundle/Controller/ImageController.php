<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Controller;

use Smart\CoreBundle\Controller\Controller;
use SmartCore\Bundle\MediaBundle\Entity\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    /**
     * @param Request $request
     * @param         $collection
     * @param         $filter
     * @param         $slug
     *
     * @return Response
     */
    public function renderAction(Request $request, $collection, $filter, $slug)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $file = $em->getRepository(File::class)->find($request->query->get('id', 0));

        $newImage = $this->get('smart_media')->generateTransformedFile($request->query->get('id', 0), $filter);

        $filter_configuration = $this->get('liip_imagine.filter.configuration')->get($filter);

        if ($file and $file->isMimeType('png')) {
            $filter_configuration['format'] = 'png';
        }

        $response = new Response($newImage);
        $response->headers->set('Content-Type', 'image/'.$filter_configuration['format']);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $data = [
            'status' => 200,
            'success' => true,
        ];

        /**
         * @var string $key
         * @var UploadedFile $file
         */
        foreach ($request->files->all() as $key => $file) {
            // @todo указание коллекции
            $id = $this->get('smart_media')->getCollection(1)->upload($file);

            $data['data'][$key] = [
                'id' => $id,
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getClientSize(),
                'mime_type'     => $file->getClientMimeType(),
                'thumbnail'     => $this->get('smart_media')->getFileUrl($id, '100x100'),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeAction(Request $request)
    {
        // @todo указание коллекции
        if ($this->get('smart_media')->getCollection(1)->remove($request->query->get('id'))) {
            $data = [
                'status' => 200,
                'success' => true,
            ];
        } else {
            $data = [
                'status' => 500,
                'success' => false,
            ];
        }

        return new JsonResponse($data);
    }
}
