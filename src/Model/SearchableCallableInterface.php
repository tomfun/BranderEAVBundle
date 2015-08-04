<?php
namespace Brander\Bundle\EAVBundle\Model;

/**
 * SearchableCallableInterface. Used to add data to elastica index.
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface SearchableCallableInterface extends SearchableEntityInterface
{
    /**
     * @return array fieldName => simple data
     */
    public function getAdditionalElasticaData();

}