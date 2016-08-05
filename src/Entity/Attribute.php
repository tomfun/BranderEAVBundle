<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Аттрибут
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_attribute")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *   "input"    = "\Brander\Bundle\EAVBundle\Entity\AttributeInput",
 *   "select"   = "\Brander\Bundle\EAVBundle\Entity\AttributeSelect",
 *   "boolean"  = "\Brander\Bundle\EAVBundle\Entity\AttributeBoolean",
 *   "numeric"  = "\Brander\Bundle\EAVBundle\Entity\AttributeNumeric",
 *   "date"     = "\Brander\Bundle\EAVBundle\Entity\AttributeDate",
 *   "textarea" = "\Brander\Bundle\EAVBundle\Entity\AttributeTextarea",
 *   "location" = "\Brander\Bundle\EAVBundle\Entity\AttributeLocation"
 * })
 * @Serializer\Discriminator(field="discr", disabled=false, map={
 *   "input"    = "Brander\Bundle\EAVBundle\Entity\AttributeInput",
 *   "select"   = "Brander\Bundle\EAVBundle\Entity\AttributeSelect",
 *   "boolean"  = "Brander\Bundle\EAVBundle\Entity\AttributeBoolean",
 *   "numeric"  = "Brander\Bundle\EAVBundle\Entity\AttributeNumeric",
 *   "date"     = "Brander\Bundle\EAVBundle\Entity\AttributeDate",
 *   "textarea" = "Brander\Bundle\EAVBundle\Entity\AttributeTextarea",
 *   "location" = "Brander\Bundle\EAVBundle\Entity\AttributeLocation"
 * })
 * @method AttributeTranslation[]|Collection getTranslations()
 */
abstract class Attribute
{
    use Translatable;
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Type("string")
     * @Serializer\ReadOnly()
     * @Serializer\Expose()
     */
    protected $id;
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Serializer\Expose()
     * @Serializer\SerializedName("isRequired")
     */
    protected $isRequired;
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Serializer\Expose()
     * @Serializer\SerializedName("isFilterable")
     */
    protected $isFilterable;
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Serializer\Expose()
     * @Serializer\SerializedName("isSortable")
     */
    protected $isSortable;
    /**
     * RESERVED
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("filterType")
     */
    protected $filterType;
    /**
     * RESERVED
     * @var string
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("filterOrder")
     */
    protected $filterOrder;
    /**
     * RESERVED
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("showType")
     */
    protected $showType;
    /**
     * @ORM\OneToMany(targetEntity="Value", cascade={"remove"}, mappedBy="attribute")
     * @Serializer\Groups(groups={"eav_attribute_values"})
     * @var Value[]
     */
    protected $values;
    /**
     * @ORM\ManyToMany(targetEntity="Brander\Bundle\EAVBundle\Entity\AttributeSet", cascade={"persist"}, mappedBy="attributes")
     * @Serializer\Groups(groups={"eav_attribute_sets"})
     * @var AttributeSet[]|Collection
     */
    protected $sets;
    /**
     * @ORM\ManyToMany(targetEntity="\Brander\Bundle\EAVBundle\Entity\AttributeGroup", cascade={"persist"}, mappedBy="attributes")
     * @Serializer\Groups(groups={"eav_attribute_groups"})
     * @var AttributeGroup[]|Collection
     */
    protected $groups;
    /**
     * @var string
     * @Serializer\Groups(groups={"eav_attribute_value_class"})
     */
    protected $valueClass;

    // -- Value ---------------------------------------
    /**
     * @ORM\OneToMany(targetEntity="AttributeTranslation", cascade={"all"}, mappedBy="translatable", orphanRemoval=true, fetch="EAGER")
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Entity\AttributeTranslation>")
     * @Serializer\Groups(groups={"translations", "admin"})
     * @Serializer\Expose()
     * @Assert\Valid
     */
    protected $translations;
//    /**
//     * *virtual
//     * @Serializer\Accessor(getter="getTitle", setter="setTitle")
//     * @Serializer\Type("string")
//     * @Serializer\Expose()
//     */
//    protected $title;
//    /**
//     * *virtual
//     * @Serializer\Accessor(getter="getHint")
//     * @Serializer\Type("string")
//     * @Serializer\Expose()
//     */
//    protected $hint;
//    /**
//     * *virtual
//     * @Serializer\Accessor(getter="getPlaceholder")
//     * @Serializer\Type("string")
//     * @Serializer\Expose()
//     */
//    protected $placeholder;

    // -- Translations ------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sets = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param string $valueClass
     */
    public function setValueClass($valueClass)
    {
        $this->valueClass = $valueClass;
    }

    /**
     * @return Value
     */
    public function createValue()
    {
        $valueClass = $this->valueClass;
        $row = new $valueClass();
        /** @var Value $row */
        $row->setAttribute($this);

        return $row;
    }

    /**
     * @param Value $value
     * @return Value
     */
    public function assignValue(Value $value)
    {
        if (get_class($value) != $this->valueClass) {
            /** @var Value $tmp */
            $valueClass = $this->valueClass;
            $tmp = new $valueClass();
            $tmp->setId($value->getId())->setAttribute($this);

            return $tmp;
        }
        $value->setAttribute($this);

        return $value;
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
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Value[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param Value[] $values
     *
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return AttributeSet[]|Collection
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * @param AttributeSet[]|Collection $sets
     *
     * @return $this
     */
    public function setSets($sets)
    {
        $this->sets = $sets;

        return $this;
    }

    /**
     * @return AttributeGroup[]|Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param AttributeGroup[]|Collection $groups
     *
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * @param boolean $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = (bool) $isRequired;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isFilterable()
    {
        return $this->isFilterable;
    }

    /**
     * @param boolean $isFilterable
     * @return $this
     */
    public function setIsFilterable($isFilterable)
    {
        $this->isFilterable = (bool) $isFilterable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->isSortable;
    }

    /**
     * @param boolean $isSortable
     *
     * @return $this
     */
    public function setIsSortable($isSortable)
    {
        $this->isSortable = (bool) $isSortable;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * @param string $filterType
     *
     * @return $this
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterOrder()
    {
        return $this->filterOrder;
    }

    /**
     * @param string $filterOrder
     *
     * @return $this
     */
    public function setFilterOrder($filterOrder)
    {
        $this->filterOrder = $filterOrder;

        return $this;
    }

}