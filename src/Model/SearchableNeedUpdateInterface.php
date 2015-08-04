<?php
namespace Brander\Bundle\EAVBundle\Model;

/**
 * not compatible with standard FOSElastica, igrvak only!
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface SearchableNeedUpdateInterface extends SearchableEntityInterface
{
    /**
     * callback for elastica.
     * @return bool
     */
    public function needUpdate();
}