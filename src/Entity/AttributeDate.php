<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 *
 * @author tomfun
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\ReadOnly()
 */
class AttributeDate extends Attribute
{

}
