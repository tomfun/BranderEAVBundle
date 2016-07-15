<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\ReadOnly()
 */
class AttributeNumeric extends Attribute
{

}
