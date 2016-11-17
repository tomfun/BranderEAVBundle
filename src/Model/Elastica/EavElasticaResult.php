<?php
namespace Brander\Bundle\EAVBundle\Model\Elastica;

use Brander\Bundle\ElasticaSkeletonBundle\Service\Elastica\ElasticaResult;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class EavElasticaResult extends ElasticaResult
{
    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"eav_result"})
     * @Serializer\SerializedName("filterableAttributes")
     * @Serializer\Type("array<Brander\Bundle\EAVBundle\Model\Elastica\FilterableAttribute>")
     * @return FilterableAttribute[]|null
     */
    public function getFilterableAttributes()
    {
        return $this->get('filterableAttributes');
    }

    /**
     * @param FilterableAttribute[] $filters
     * @return $this
     */
    public function setFilterableAttributes($filters)
    {
        if (!is_array($this->extra)) {
            $this->extra = [];
        }
        $this->extra['filterableAttributes'] = $filters;

        return $this;
    }
}