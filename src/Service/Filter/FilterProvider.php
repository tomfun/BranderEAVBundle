<?php
namespace Brander\Bundle\EAVBundle\Service\Filter;

use Brander\Bundle\EAVBundle\Model\Filter\FilterModelProviderInterface;

/**
 * Service to work with js filter attributes
 *
 * @author Tomfun <tomfun1990@gmail.com>
 */
class FilterProvider implements FilterModelProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getAvailableFilterModels()
    {
        return [
            'brander-eav/listing/bucketRangeFilterView',
            'brander-eav/listing/simpleFilterView',
            'brander-eav/listing/simpleRangeFilterView',
        ];
    }
}