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
    const RANGE_FILTER_SIMPLE = 'brander-eav/listing/simpleRangeFilterView';
    const RANGE_FILTER_CUSTOM = 'brander-eav/listing/bucketRangeFilterView';

    /**
     * @inheritdoc
     */
    public function getAvailableFilterModels()
    {
        return [
            'brander-eav/listing/simpleFilterView',
            self::RANGE_FILTER_SIMPLE,
            self::RANGE_FILTER_CUSTOM,
        ];
    }
}