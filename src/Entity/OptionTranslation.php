<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_option_translation")
 * @method AttributeSelectOption getTranslatable()
 * @method $this setTranslatable(AttributeSelectOption $attributeSelectOption)
 */
class OptionTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Brander\Bundle\EAVBundle\Entity\AttributeSelectOption", inversedBy="translations", fetch="EAGER")
     * @ORM\JoinColumn(name="translatable", referencedColumnName="id", nullable=false)
     * @var
     */
    protected $translatable;

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