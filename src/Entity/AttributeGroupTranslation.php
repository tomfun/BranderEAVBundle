<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_group_translation")
 * @method $this setTranslatable(AttributeGroup $attribute)
 * @method AttributeGroup getTranslatable()
 */
class AttributeGroupTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Brander\Bundle\EAVBundle\Entity\AttributeGroup", inversedBy="translations", fetch="EAGER")
     * @ORM\JoinColumn(name="translatable", referencedColumnName="id", nullable=false)
     * @var
     */
    protected $translatable;
    /**
     * spike
     * @param int $id
     * @deprecated
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
