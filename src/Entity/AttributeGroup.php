<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Werkint\Bundle\FrameworkExtraBundle\Model\Translatable;

/**
 * AttributeGroup.
 *
 * Группа атрибутов
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 *
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_attribute_group")
 * Переводные методы:
 * @method AttributeGroupTranslation translate(string $lang)
 * @method AttributeGroupTranslation[]|ArrayCollection getTranslations()
 * @method AttributeGroupTranslation[] getATranslations()
 * @method AttributeGroupTranslation mergeNewTranslations()
 * @method string getTitle()
 * *method AttributeGroupTranslation setTitle(string $title)
 */
class AttributeGroup
{
    /**
     * @inheritdoc
     */
    public function __construct(){
        $this->setAttributes(new ArrayCollection());
    }
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Type("string")
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="brander.eav.class")
     * @Serializer\Type("string")
     */
    protected $class;

    /**
     * @ORM\ManyToMany(targetEntity="\Brander\Bundle\EAVBundle\Entity\Attribute", cascade={"persist"}, inversedBy="groups")
     * @ORM\JoinTable(name="brander_eav_attribute_groups_attributes",
     *   joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")}
     * )
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Entity\Attribute>")
     * @var Attribute[]|ArrayCollection
     */
    protected $attributes;

    // -- Translations ------------------------------------

    use Translatable;

    /**
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Entity\AttributeGroupTranslation>")
     * @Serializer\Accessor(getter="getATranslations", setter="setATranslations")
     * @Serializer\Groups({"=g('translations') || g('admin')"})
     * @Serializer\Expose()
     * @Assert\Valid
     */
    protected $translations;

    /**
     * *virtual
     * @Serializer\Accessor(getter="getTitle", setter="setTitle")
     * @Serializer\Type("string")
     * @Serializer\Groups({"=read && !g('minimal')"})
     * @Serializer\Expose()
     */
    protected $title;

    // -- Accessors ---------------------------------------

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return Attribute[]|ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[]|ArrayCollection $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }
}