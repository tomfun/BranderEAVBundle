<?php
namespace Brander\Bundle\EAVBundle\Model\Elastica;

use Brander\Bundle\ElasticaSkeletonBundle\Entity\Aggregation;
use Brander\Bundle\ElasticaSkeletonBundle\Service\Elastica\ElasticaResult;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class EavElasticaResult extends ElasticaResult
{
    /**
     * Universal getter
     *
     * @param string $name
     * @return mixed|null
     */
    protected function get($name)
    {
        return isset($this->extra[$name]) ? $this->extra[$name] : null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"=read"})
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

    /**
     * @param mixed       $value
     * @param Aggregation $metadata
     * @return $this
     */
    public function setAutoAggregation($value, $metadata)
    {
        if (!is_array($this->extra)) {
            $this->extra = [];
        }
        $name = isset($metadata->getExtra()['serializeName'])
            ? $metadata->getExtra()['serializeName'] : $metadata->getName();
        switch ($metadata->getExtra()['type']) {
            case 'range_basket':
                $this->extra['aggregations'][$name][$metadata->getExtra()['type']] = $value;
                break;
            default:
                $this->extra['aggregations'][$name][$metadata->getType()] = $value;
                break;
        }
        return $this;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"=read"})
     * @Serializer\SerializedName("aggregations")
     * @Serializer\Type("array")
     * @return array|null
     */
    public function getAutoAggregations()
    {
        return $this->get('aggregations');
    }
}