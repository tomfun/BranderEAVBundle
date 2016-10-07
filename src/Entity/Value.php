<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Значение аттрибута
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity(repositoryClass="Brander\Bundle\EAVBundle\Repo\Value")
 * @ORM\Table(name="brander_eav_value")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *   "input"     = "\Brander\Bundle\EAVBundle\Entity\ValueInput",
 *   "boolean"   = "\Brander\Bundle\EAVBundle\Entity\ValueBoolean",
 *   "select"    = "\Brander\Bundle\EAVBundle\Entity\ValueSelect",
 *   "numeric"   = "\Brander\Bundle\EAVBundle\Entity\ValueNumeric",
 *   "date"      = "\Brander\Bundle\EAVBundle\Entity\ValueDate",
 *   "textarea"  = "\Brander\Bundle\EAVBundle\Entity\ValueTextarea",
 *   "location"  = "\Brander\Bundle\EAVBundle\Entity\ValueLocation"
 * })
 * @Serializer\Discriminator(field="discr", map={
 *   "input"     = "Brander\Bundle\EAVBundle\Entity\ValueInput",
 *   "boolean"   = "Brander\Bundle\EAVBundle\Entity\ValueBoolean",
 *   "select"    = "Brander\Bundle\EAVBundle\Entity\ValueSelect",
 *   "numeric"   = "Brander\Bundle\EAVBundle\Entity\ValueNumeric",
 *   "date"      = "Brander\Bundle\EAVBundle\Entity\ValueDate",
 *   "textarea"  = "Brander\Bundle\EAVBundle\Entity\ValueTextarea",
 *   "location"  = "Brander\Bundle\EAVBundle\Entity\ValueLocation"
 * })
 */
abstract class Value
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Type("string")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string",nullable=true)
     * @Serializer\Accessor(getter="getValue",setter="setValue")
     * @var string|null
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="Attribute", cascade={"persist"}, inversedBy="values")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Serializer\Accessor(getter="getAttribute",setter="setAttribute")
     * @Serializer\Type("Brander\Bundle\EAVBundle\Entity\Attribute")
     * @Serializer\Groups("values_with_attributes")
     * @var Attribute
     */
    protected $attribute;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("attributeTranslations")
     * @Serializer\Groups("translations_value_view")
     */
    public function getAttributeTranslations()
    {
        return $this->getAttribute()->getTranslations();
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getValue();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !(bool) $this->getValue();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return !$this->isEmpty();
    }

    // -- Accessors ---------------------------------------

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @deprecated
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    final public function getValueRaw()
    {
        return $this->value;
    }

    /**
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function setAttribute(Attribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }
}
