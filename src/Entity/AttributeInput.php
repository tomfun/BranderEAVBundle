<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Инпут
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\ReadOnly()
 */
class AttributeInput extends Attribute
{
}