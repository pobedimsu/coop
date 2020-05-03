<?php

declare(strict_types=1);

namespace App\Media;

use SmartCore\Bundle\MediaBundle\Service\AbstractCollectionService;
use SmartCore\Bundle\MediaBundle\Service\MediaCloudService;

class MyMediaCollection extends AbstractCollectionService
{
    public function __construct(MediaCloudService $mc)
    {
        parent::__construct($mc->getEntityManager());
    }
}
