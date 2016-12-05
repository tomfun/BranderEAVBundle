<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Negotiation\Exception\InvalidArgument;

/**
 * Значение аттрибута для мультиселекта
 *
 * @author mom <alinyonish@gmail.com>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueMultiSelect extends Value
{
    /**
     * @var AttributeSelectOption[]|Collection
     */
    protected $options = [];

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("selectedOptions")
     * @Serializer\Groups("eav_value_view_title_translations")
     * @return string[]
     */
    public function getSelectedOptions()
    {
        $translations = [];
        foreach ($this->getOptions() as $option) {
            foreach ($option->getTranslations() as $translation) {
                $translations[] = [$translation->getLocale() => $translation->getTitle()];
            }
        }

        return $translations;
    }

    /**
     * @return AttributeSelectOption[]|Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param AttributeSelectOption[]|Collection $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->setValue($options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($values)
    {
        if (is_array($values)) {
            $valIds = [];
            $this->options = $this->options instanceof Collection ? $this->options : new ArrayCollection();
            $this->options->clear();
            foreach ($values as $value) {
                if (!($value instanceof AttributeSelectOption)) {
                    throw new \InvalidArgumentException("wrong value type");
                }
                $this->options->add($value);
                $valIds[] = $value->getId();
            }
            $valIds = array_unique($valIds);
            $successSort = asort($valIds);
            if (!$successSort) {
                throw new \InvalidArgumentException("sort error");
            }

            return parent::setValue(implode(',', $valIds));
        } else {
            $sortedValues = explode(',', $values);
            $sortedValues = array_unique($sortedValues);
            $successSort = asort($sortedValues);
            if (!$successSort) {
                throw new \InvalidArgumentException("sort error");
            }

            return parent::setValue(implode(',', $sortedValues));
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        /** @var AttributeSelect $attr */
        $attr = $this->getAttribute();
        $baseValid = parent::isValid();
        $optionsValid = true;
        // todo: $this->getOptions() после десериализации равен null
        foreach ($this->getOptions() as $option) {
            $containOption = $attr->getOptions()->contains($option);
            $containOption = $containOption || $attr->getOptions()
                    ->map(function (AttributeSelectOption $opt) {
                        return $opt->getId();
                    })
                    ->contains((int) $this->getValue());
            $optionsValid = $optionsValid && $containOption;
        }

        return $baseValid && $optionsValid;
    }
}
