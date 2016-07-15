<?php
namespace Brander\Bundle\EAVBundle\Entity;

/**
 * AttributeSetAwareInterface.
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface AttributeSetAwareInterface
{
    /**
     * @return AttributeSet
     */
    public function getAttributeSet();
}
