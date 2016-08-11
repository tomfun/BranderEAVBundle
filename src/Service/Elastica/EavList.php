<?php
namespace Brander\Bundle\EAVBundle\Service\Elastica;

use Brander\Bundle\EAVBundle\Model\Elastica\EavElasticaQuery;
use Brander\Bundle\EAVBundle\Model\Elastica\EavElasticaResult;
use Brander\Bundle\EAVBundle\Service\Serialize\QueryDeserializeHolder;
use Brander\Bundle\ElasticaSkeletonBundle\Service\Elastica\ElasticaList;
use Pagerfanta\Pagerfanta;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class EavList extends ElasticaList
{
    /** @var string */
    protected $resultClass;
    /** @var string */
    protected $queryClass;
    /** @var QueryDeserializeHolder */
    protected $queryHolder;

    /**
     * @return string
     */
    public function getQueryClass()
    {
        return $this->queryClass;
    }

    /**
     * @param string $queryClass
     *
     * @return $this
     */
    public function setQueryClass($queryClass)
    {
        $this->queryClass = $queryClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * @param string $resultClass
     *
     * @return $this
     */
    public function setResultClass($resultClass)
    {
        $this->resultClass = $resultClass;

        return $this;
    }

    /**
     * @return QueryDeserializeHolder
     */
    public function getQueryHolder()
    {
        return $this->queryHolder;
    }

    /**
     * @param QueryDeserializeHolder $queryHolder
     *
     * @return $this
     */
    public function setQueryHolder(QueryDeserializeHolder $queryHolder)
    {
        $this->queryHolder = $queryHolder;

        return $this;
    }

    /**
     * @param EavElasticaQuery $query
     * @param array|null       $orderMap
     * @return EavElasticaResult
     * @throws \Exception
     */
    public function result($query, array $orderMap = null)
    {
        if (is_subclass_of($query, $this->getQueryClass()) || get_class($query) === $this->getQueryClass()) {
            $this->getQueryHolder()->initializeQuery($query);

            return parent::result($query, $orderMap);
        }
        throw new \Exception('Wrong query');
    }

    /**
     * @example return new ElasticaResult($rows, $page, $countPage, $countTotal);
     * @param Pagerfanta $data
     * @param            $rows
     * @param            $page
     * @param            $countPage
     * @param            $countTotal
     * @return EavElasticaResult
     */
    protected function createResult(Pagerfanta $data, $rows, $page, $countPage, $countTotal)
    {
        $class = $this->getResultClass();

        return new $class($rows, $page, $countPage, $countTotal);
    }
}