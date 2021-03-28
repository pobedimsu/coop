<?php

namespace SmartCore\Bundle\TexterBundle\Twig;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use SmartCore\Bundle\TexterBundle\Entity\Text;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TexterExtension extends AbstractExtension
{
    /** @var Cache */
//    protected $cache;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, ?Cache $cache = null)
    {
        $this->cache = $cache;
        $this->em    = $em;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('texter', [$this, 'getText'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getText($name)
    {
        $text = $this->em->getRepository(Text::class)->findOneBy(['name' => $name]);

        if ($text) {
            return $text->getText();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'texter_extension';
    }
}
