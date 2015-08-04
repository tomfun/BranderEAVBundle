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
    public function __construct()
    {
        // TODO: удалять, если false
        $this->setValue(true);
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getAttribute()->getTitle();
    }
}
