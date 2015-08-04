<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method $this setTranslatable(Attribute $attribute)
 * @method Attribute getTranslatable()
 * @ORM\Entity()
 * @ORM\Table(name="brander_eav_attribute_translation")
 */
class AttributeTranslation extends AbstractTranslation
{
    /**
     * Подсказка
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups("=read || g('admin')")
     * @Serializer\Expose()
     */
    protected $hint;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups("=read || g('admin')")
     * @Serializer\Expose()
     */
    protected $placeholder;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups("=read || g('admin')")
     * @Serializer\Expose()
     */
    protected $postfix;

    /**
     * spike
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * @param string $hint
     * @return $this
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostfix()
    {
        return $this->postfix;
    }

    /**
     * @param string $postfix
     * @return $this
     */
    public function setPostfix($postfix)
    {
        $this->postfix = $postfix;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->title) && empty($this->hint) && empty($this->placeholder) && empty($this->postfix);
    }
}