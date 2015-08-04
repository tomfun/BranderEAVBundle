<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Werkint\Bundle\FrameworkExtraBundle\Model\Translatable;

/**
 * Варианты для селекта
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_attribute_select_option")
 * @method string getTitle()
 * @method OptionTranslation translate(string $lang)
 * @Serializer\ExclusionPolicy("all")
 */
class AttributeSelectOption
{

    // -- Entity ---------------------------------------

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Serializer\ReadOnly()
     * @var int
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="AttributeSelect", inversedBy="options")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     * @var AttributeSelect
     */
    protected $attribute;
//
//    /**
//     * @ORM\Column(type="string")
//     * @Serializer\Type("string")
//     * @Serializer\Groups({"=g('admin') || read"})
//     * @Serializer\Expose()
//     * @var string
//     */
//    protected $title;

    // -- Translations ------------------------------------

    use Translatable;

    /**
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Entity\OptionTranslation>")
     * @Serializer\Groups({"=read || g('admin')"})
     * @Serializer\Accessor(getter="getATranslations", setter="setATranslations")
     * @Serializer\Groups({"=g('translations') || g('admin')"})
     * @Serializer\Expose()
     * @Assert\Valid
     */
    protected $translations;

    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationEntityClass()
    {
        return OptionTranslation::class;
    }

    /**
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
     * @return AttributeSelect
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param AttributeSelect $attribute
     * @return $this
     */
    public function setAttribute(AttributeSelect $attribute = null)
    {
        $this->attribute = $attribute;
        return $this;
    }
//
//    /**
//     * @return string
//     */
//    public function getTitle()
//    {
//        return $this->title;
//    }

//    /**
//     * @param string $title
//     * @return $this
//     */
//    public function setTitle($title)
//    {
//        $this->title = $title;
//        return $this;
//    }
}