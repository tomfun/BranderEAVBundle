<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Значение аттрибута для селекта
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueSelect extends Value
{
    /**
     * @var AttributeSelectOption
     */
    protected $option = null;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("selectedOption")
     * @Serializer\Groups("eav_value_view_title_translations")
     */
    public function getSelectedOption()
    {
        return array_map(function (AbstractTranslation $translation) {
            return $translation->getTitle();
        }, $this->getOption()->getTranslationsByLocale());
    }

    /**
     * @return AttributeSelectOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param AttributeSelectOption $option
     * @return $this
     */
    public function setOption(AttributeSelectOption $option = null)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return !$this->getOption() ? null : $this->getOption()->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        // TODO: подтягивать option
        if ($value instanceof AttributeSelectOption) {
            $this->setOption($value);
            $value = $value->getId();
        }

        return parent::setValue($value);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        /** @var AttributeSelect $attr */
        $attr = $this->getAttribute();

        return parent::isValid() && (in_array($this->getValue(), $attr->getOptions()->getKeys()) || $attr->getOptions()->contains($this->getOption()));
    }
}
