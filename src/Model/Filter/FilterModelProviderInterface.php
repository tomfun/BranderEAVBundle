<?php
namespace Brander\Bundle\EAVBundle\Model\Filter;

/**
 * FilterProviderInterface.
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface FilterModelProviderInterface
{
    public function getAvailableFilterModels();
}