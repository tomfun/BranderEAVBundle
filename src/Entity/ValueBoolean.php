<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Значение аттрибута для boolean
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueBoolean extends Value
{

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !($this->value == true || $this->value === false);
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getAttribute()->getTitle();
    }
}
