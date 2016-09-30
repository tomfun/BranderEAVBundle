<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

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
    /**
     * @Assert\Regex(pattern="/^-?\d+(\.\d+)?$/", message="must be a valid number")
     */
    protected $value;
}
