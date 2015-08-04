<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Значение аттрибута для numeric
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueNumeric extends Value
{

}
