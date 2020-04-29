<?php

namespace SmartCore\Bundle\MediaBundle\Twig;

use SmartCore\Bundle\MediaBundle\Service\MediaCloudService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MediaExtension extends AbstractExtension
{
    /**
     * @var MediaCloudService
     */
    protected $media;

    /**
     * Constructor.
     */
    public function __construct($media)
    {
        $this->media = $media;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('smart_media',     [$this, 'generateFileUrl'], ['is_safe' => ['html']]),
            new TwigFunction('smart_media_img', [$this, 'renderImgTag'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param  int          $id
     * @param  string|null  $filter
     *
     * @return string
     */
    public function generateFileUrl($id, $filter = null)
    {
        return (empty($id)) ? null : $this->media->getFileUrl($id, $filter);
    }

    /**
     * @param  int          $id
     * @param  string|null  $filter
     * @param  string       $alt
     *
     * @return null|string
     */
    public function renderImgTag($id, $filter = null, $alt = '')
    {
        return (empty($id)) ? null : '<img src="'.$this->generateFileUrl($id, $filter).'" alt="'.$alt.'">';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'smart_media_twig_extension';
    }
}
