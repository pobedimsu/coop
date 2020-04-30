<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @todo
 *
 * @ORM\Entity()
 * @ORM\Table(name="media_filters")
 * @UniqueEntity(fields={"name"}, message="Name must be unique")
 */
class Filter
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\NameUnique;
    use ColumnTrait\Title;

    public function __construct()
    {
        $this->created_at       = new \DateTime();
    }
}
