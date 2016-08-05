<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Варианты для селекта
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_attribute_select_option")
 * @Serializer\ExclusionPolicy("all")
 * @method OptionTranslation[]|Collection getTranslations()
 */
class AttributeSelectOption
{
    use Translatable;

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

    /**
     * @ORM\OneToMany(targetEntity="Brander\Bundle\EAVBundle\Entity\OptionTranslation", cascade={"all"}, mappedBy="translatable", orphanRemoval=true, fetch="EAGER")
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Entity\OptionTranslation>")
     * @Serializer\Groups(groups={"translations", "admin"})
     * @Serializer\Expose()
     * @Assert\Valid
     */
    protected $translations;
//    /**
//     * @Serializer\Accessor(getter="getTitle", setter="setTitle")
//     * @Serializer\Type("string")
//     * @Serializer\Groups({"=read && !g('minimal')"})
//     * @Serializer\Expose()
//     */
//    protected $title;

    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationEntityClass()
    {
        return OptionTranslation::class;
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
    /**
     * @Serializer\PostDeserialize()
     */
    public function updateTranslations()
    {
        $translatable = $this;
        $this->getTranslations()->map(function (OptionTranslation $trans) use (&$translatable) {
            $trans->setTranslatable($translatable);
        });
    }
}