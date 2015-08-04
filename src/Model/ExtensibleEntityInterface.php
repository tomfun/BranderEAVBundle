<?php
namespace Brander\Bundle\EAVBundle\Model;

use Brander\Bundle\EAVBundle\Entity\AttributeSet;
use Brander\Bundle\EAVBundle\Entity\Value;

/**
 * ExtensibleEntityInterface.
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface ExtensibleEntityInterface
{
    /**
     * @return AttributeSet
     */
    public function getAttributeSet();

    /**
     * @return Value[]
     */
    public function getValues();

}