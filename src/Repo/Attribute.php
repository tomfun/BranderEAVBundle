<?php
namespace Brander\Bundle\EAVBundle\Repo;

use Doctrine\ORM\EntityRepository;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class Attribute extends EntityRepository
{
    /**
     * Return list of filterable or sortable attributes
     *
     * @param array $usedIds attribute ids
     * @param bool  $isSortable
     * @param bool  $isFilterable
     * @return \Brander\Bundle\EAVBundle\Entity\Attribute[]
     */
    public function getAvailableAttributes(array $usedIds = [], $isSortable = true, $isFilterable = true)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->eq('a.isSortable', $qb->expr()->literal($isSortable)))
            ->orWhere($qb->expr()->eq('a.isFilterable', $qb->expr()->literal($isFilterable)));
        if (count($usedIds)) {
            $qb->andWhere('a.id in (:ids)')
                ->setParameter('ids', $usedIds);
        }

        return $qb->getQuery()->getResult();
    }
}