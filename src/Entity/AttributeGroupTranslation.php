<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method $this setTranslatable(AttributeGroup $attribute)
 * @method AttributeGroup getTranslatable()
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_group_translation")
 */
class AttributeGroupTranslation extends AbstractTranslation
{
    /**
     * spike
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
