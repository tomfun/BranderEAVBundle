<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Аттрибут
 *
 * @author tomfun
 *
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_attribute_set")
 * @Serializer\ExclusionPolicy("all")
 *
 */
class AttributeSet
{
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
     * @var string
     *
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     */
    protected $title;
//
//    /**
//     * @var AttributeSetAwareInterface
//     *
//     * @ORM\OneToMany(targetEntity="AttributeSetAwareInterface")
//     */
//    protected $entities;

    /**
     * @ORM\ManyToMany(targetEntity="\Brander\Bundle\EAVBundle\Entity\Attribute", cascade={"persist"}, inversedBy="sets")
     * @ORM\JoinTable(name="brander_eav_sets_attributes",
     *   joinColumns={@ORM\JoinColumn(name="set_id", referencedColumnName="id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")}
     * )
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Entity\Attribute>")
     * *Serializer\Groups({"Default", "admin", "attributes"})
     * @Serializer\Groups({"attributes"})
     * @Serializer\Expose()
     * @var Attribute[]|Collection
     */
    protected $attributes;

    /**
     * set default attributes
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Attribute[]|Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[]|Collection $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return AttributeSetAwareInterface
     */
    public function getEntities()
    {
        return $this->entities;
    }

}