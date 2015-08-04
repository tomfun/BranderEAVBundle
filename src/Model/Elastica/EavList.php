<?php
namespace Brander\Bundle\EAVBundle\Model\Elastica;

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

    /**
     * @param EavElasticaQuery $query
     * @param array|null $orderMap
     * @return EavElasticaResult
     * @throws \Exception
     */
    public function result($query, array $orderMap = null)
    {
        if (is_subclass_of($query, $this->getQueryClass()) || get_class($query) === $this->getQueryClass()) {
            return parent::result($query, $orderMap);
        }
        throw new \Exception('Wrong query');
    }
}