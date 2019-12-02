<?php

namespace SmartCore\Bundle\TexterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\Bundle\TexterBundle\Model\TextModel;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="SmartCore\Bundle\TexterBundle\Repository\TextRepository")
 * @ORM\Table(name="texter_items")
 * @UniqueEntity(fields={"name"}, message="Имя текста должно быть уникальным.")
 */
class Text extends TextModel
{
}
