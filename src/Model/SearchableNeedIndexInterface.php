<?php
namespace Brander\Bundle\EAVBundle\Model;

/**
 * SearchableEntityInterface.
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface SearchableNeedIndexInterface extends SearchableEntityInterface
{
    /**
     * callback for elastica. whether need index - true/delete document - false
     * @return bool
     */
    public function needIndex();
}