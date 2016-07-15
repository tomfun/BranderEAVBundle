<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method $this setTranslatable(AttributeSelectOption $attribute)
 * @method AttributeSelectOption getTranslatable()
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_option_translation")
 */
class OptionTranslation extends AbstractTranslation
{
    /**
     * @return mixed
     */
    public static function getTranslatableEntityClass()
    {
        return AttributeSelectOption::class;
    }

    /**
     * spike
     * @deprecated
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}