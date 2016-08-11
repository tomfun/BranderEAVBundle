<?php
namespace Brander\Bundle\EAVBundle\Repo;

use Brander\Bundle\EAVBundle\Entity\AttributeSelect;
use Brander\Bundle\EAVBundle\Entity\AttributeSelectOption;
use Brander\Bundle\EAVBundle\Entity\ValueDate;
use Brander\Bundle\EAVBundle\Entity\ValueNumeric;
use Doctrine\ORM\EntityRepository;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class Value extends EntityRepository
{
    /**
     * @param AttributeSelect $attribute
     * @return AttributeSelectOption[]
     */
    public function getUsedSelectOptions(AttributeSelect $attribute)
    {
        $qb = $this->createQueryBuilder('v');
        $qb->select('v.value')
            ->leftJoin('v.attribute', 'a')
            ->where('a = :attribute')
            ->andWhere('v instance of BranderEAVBundle:ValueSelect')
            ->andWhere('a instance of BranderEAVBundle:AttributeSelect')
            ->setParameter('attribute', $attribute)
            ->groupBy('v.value');
        $optionIds = array_map(function ($v) {
            return $v['value'];
        }, $qb->getQuery()
            ->getArrayResult());
        $optionRepo = $this->_em->getRepository(AttributeSelectOption::class);
        $options = $optionRepo->findBy(['id' => $optionIds]);

        return $options;
    }

    /**
     * @param int $attributeId
     * @return float[] - [min, max]
     */
    public function minMaxByAttributeId($attributeId)
    {
        $driver = $this->getEntityManager()->getConnection()->getDriver();
        $castSupported = in_array($driver->getName(), ['mysql', 'mysql2', 'pdo_mysql']);
        if ($castSupported) {
            $sql = '
               SELECT MAX(CAST(v.value as DECIMAL)) as `max`,
                      MIN(CAST(v.value as DECIMAL)) as `min`
               FROM brander_eav_value v
               WHERE attribute_id = :attributeId;';
            $params = [
                'attributeId' => $attributeId,
            ];
            $data = $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetch();
            if ($data === false) {
                return false;
            }

            return [
                'max' => floatval($data['max']),
                'min' => floatval($data['min']),
            ];
        }
        $qb = $this->createQueryBuilder('v');
        $qb->leftJoin('v.attribute', 'a')
            ->where('a = :attribute')
            ->setParameter('attribute', $attributeId);

        $arr = [];
        foreach ($qb->getQuery()->getResult() as $value) {
            if ($value instanceof ValueNumeric && is_numeric($value->getValue())) {
                $arr[] = floatval($value->getValue());
            } elseif ($value instanceof ValueDate && is_numeric($value->getValueRaw())) {
                $arr[] = floatval($value->getValueRaw());
            }
        }
        if (!count($arr)) {
            return false;
        }

        return [
            'max' => max($arr),
            'min' => min($arr),
        ];

    }


}