<?php
namespace Brander\Bundle\EAVBundle\Model\Elastica;

use Brander\Bundle\EAVBundle\Entity\Attribute;
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
     * @return Attribute[]|null
     */
    public function getFilterableAttributes()
    {
        return $this->get('filterableAttributes');//todo: in query make filterable attrs
    }
}