<?php
namespace Brander\Bundle\EAVBundle\Model\Elastica;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class FilterableAttribute
{
    /**
     * @example ["search"]  -> filter.get('search')
     * @example ["attribute", "9"]   -> filter.get('attribute')["9"]
     * @Serializer\Type("array<string>")
     * @var  string[] - path for filter model field
     */
    protected $field;

    /**
     * @Serializer\Type("string")
     * @var  string - require js view name. must be one of available.
     */
    protected $view;

    /**
     * @Serializer\Type("Brander\Bundle\EAVBundle\Entity\Attribute")
     * @var  Attribute
     */
    protected $attribute;

    /**
     * @Serializer\Type("array<string, string>")
     * @var  string[] - js hash -> merge this options to create view
     */
    protected $viewOptions;

    /**
     * @Serializer\Type("integer")
     * @var  int
     */
    protected $viewOrder;

    /**
     * @Serializer\Type("boolean")
     * @var  boolean
     */
    protected $isFilterable;

    /**
     * @Serializer\Type("boolean")
     * @var  boolean
     */
    protected $isSortable;

    /**
     * @return \string[]
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param \string[] $field
     *
     * @return $this
     */
    public function setField(array $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getViewOptions()
    {
        return $this->viewOptions;
    }

    /**
     * @param \string[] $viewOptions
     *
     * @return $this
     */
    public function setViewOptions(array $viewOptions)
    {
        $this->viewOptions = $viewOptions;
        return $this;
    }

    /**
     * @return int
     */
    public function getViewOrder()
    {
        return $this->viewOrder;
    }

    /**
     * @param int $viewOrder
     *
     * @return $this
     */
    public function setViewOrder($viewOrder)
    {
        $this->viewOrder = $viewOrder;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsFilterable()
    {
        return $this->isFilterable;
    }

    /**
     * @param boolean $isFilterable
     *
     * @return $this
     */
    public function setIsFilterable($isFilterable)
    {
        $this->isFilterable = (bool)$isFilterable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsSortable()
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
        $this->isSortable = (bool)$isSortable;
        return $this;
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
     *
     * @return $this
     */
    public function setAttribute(Attribute $attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }
}