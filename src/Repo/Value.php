<?php
namespace Brander\Bundle\EAVBundle\Repo;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\ValueSelect;
use Doctrine\ORM\EntityRepository;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class Value extends EntityRepository
{
    /**
     * @param Attribute $attribute
     * @return ValueSelect[]
     */
    public function getUsed(Attribute $attribute)
    {
        $qb = $this->createQueryBuilder('v');
        $qb->leftJoin('v.attribute', 'a')
           ->where('a = :attribute')
           ->setParameter('attribute', $attribute)
           ->groupBy('v.value');
        return $qb->getQuery()
                  ->getResult();
    }

}