<?php
/**
 * EavElasticaResult.php
 *
 * @category   Brander
 * @package    Brander_EavElasticaResult.php
 * @author     brander.ua
 */

namespace Brander\Bundle\EAVBundle\Model\Elastica;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\AttributeBoolean;
use Brander\Bundle\EAVBundle\Entity\AttributeDate;
use Brander\Bundle\EAVBundle\Entity\AttributeInput;
use Brander\Bundle\EAVBundle\Entity\AttributeLocation;
use Brander\Bundle\EAVBundle\Entity\AttributeNumeric;
use Brander\Bundle\EAVBundle\Entity\AttributeTextarea;
use Brander\Bundle\EAVBundle\Entity\ValueLocation;
use Brander\Bundle\EAVBundle\Model\GeoLocation;
use Brander\Bundle\ElasticaSkeletonBundle\Service\Elastica\ElasticaQuery;
use Doctrine\ORM\EntityRepository;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
abstract class EavElasticaQuery extends ElasticaQuery
{
    const GEO_UNITS_KM = 'km';
    const GEO_UNITS_M = 'm';
    const GEO_UNITS_MI = 'mi';
    const GEO_UNITS_YD = 'yd';
    const GEO_UNITS_FT = 'ft';
    const GEO_UNITS_NM = 'NM';

    const RANGE_GT = 'gt';
    const RANGE_GTE = 'gte';
    const RANGE_LTE = 'lte';
    const RANGE_LT = 'lt';

    /** @var  EntityRepository */
    protected $attributeRepository;

    /**
     * 'attribute' field comes from frontend can be some like:
     * {
     *  "10": "1"//10 - id of AttributeSelect entity that can be filtered
     *  "11": "buy milk"// 11 - id of AttributeInput or AttributeTextarea, full text search
     *  "2": "gte:2003-06-15T00:00:00+02:00;"// 2 - AttributeDate
     *  "2": "2003-06-15"// 2 - AttributeDate, but this is bad idea (this not the range as above, this exact field search)
     *  "8": "lt:255;"// AttributeInput
     * }
     * @Serializer\SerializedName("attributes")
     * @Serializer\Type("array<integer, string>")
     * @var string[]
     */
    protected $attributesRaw;

    /**
     * Если установлен в тру, при поиске атрибутов типа инпат будут более жёсткие критерии поиска
     * @Serializer\Type("boolean")
     * @var bool
     */
    protected $findOptionInputHard = false;

    /**
     * Если установлен в тру, при поиске атрибутов типа текстовое поле будут более жёсткие критерии поиска
     * @Serializer\Type("boolean")
     * @var bool
     */
    protected $findOptionTextareaHard = false;

    /**
     * @Serializer\Exclude()
     * @var Attribute[]
     */
    protected $attributes;

    /**
     * @return EntityRepository
     */
    public function getAttributeRepository()
    {
        return $this->attributeRepository;
    }

    /**
     * @param EntityRepository $attributeRepository
     *
     * @return $this
     */
    public function setAttributeRepository(EntityRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAttributesRaw()
    {
        return $this->attributesRaw;
    }

    /**
     * @param string[] $attributesRaw
     * @return $this
     */
    public function setAttributesRaw(array $attributesRaw)
    {
        $this->attributesRaw = $attributesRaw;
        return $this;
    }

    /**
     * Add geo distance filter. $value must consist of lat,lng,distance.
     * Example $value = '17.95,14.91distance:1'//km
     * Example $value = '17.95, 14.91 distance: 12.07 mi'
     * value format: 'LAT, LON distance: DIST UNIT'
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/filter-by-geopoint.html
     *
     * @param AttributeLocation $attr
     * @param mixed $value
     * @return \Elastica\Filter\GeoDistance
     */
    protected function getGeoLocationFilter(AttributeLocation $attr, $value)
    {
        $value = trim($value);
        if ($value) {
            if (strpos($value, 'distance:')) {
                list($value, $distance) = explode('distance:', $value);
                $value = new GeoLocation($value);
                $units = [
                    self::GEO_UNITS_KM,
                    self::GEO_UNITS_M,
                    self::GEO_UNITS_MI,
                    self::GEO_UNITS_YD,
                    self::GEO_UNITS_FT,
                    self::GEO_UNITS_NM,
                ];
                $matches = [];
                preg_match('/\s*([\d\.,]+)(.*)$/', $distance, $matches);
                if (isset($matches[1]) && $matches[1] !== '') {
                    $distance = floatval($matches[1]);
                    if (isset($matches[1]) && ($unit = strtolower(trim($matches[2]))) !== '') {
                        if (!in_array($unit, $units)) {
                            $unit = self::GEO_UNITS_KM;
                        }
                    } else {
                        $unit = self::GEO_UNITS_KM;
                    }
                    $distance .= $unit;
                    $fieldName = $attr->getId() . ValueLocation::ELASTICA_POSTFIX;
                    $geoQuery = new \Elastica\Filter\GeoDistance($fieldName, (string)$value, $distance);
                    return $geoQuery;
                } else {
                    throw new \InvalidArgumentException("wrong geo format");
                }
            }
        }
        return null;
    }

    /**
     * @example "gt:0;lt:101;" - number range
     * @example "gte:5;lte:15;" - number range
     * @param string $value
     * @param callable|null $formatter
     * @return array|null
     */
    protected function parseRange($value, $formatter = null)
    {
        $keywords = [
            self::RANGE_GT,
            self::RANGE_GTE,
            self::RANGE_LT,
            self::RANGE_LTE,
        ];
        $result = [];
        $res = [];
        foreach($keywords as $keyword) {
            $format = $keyword . ':\s*(.+?)\s*;';
            preg_match('/' . $format . '/i', $value, $res);
            if ($res && count($res) > 1) {
                if ($formatter) {
                    $result[$keyword] = call_user_func($formatter, $res[1]);
                } else {
                    $result[$keyword] = $res[1];
                }
            }
        }
        if (count($result)) {
            return $result;
        }
        return null;
    }

    /**
     * Add just geo distance filters
     * @return \Elastica\Filter\GeoDistance[]
     */
    public function getFilters()
    {
        $res = [];
        if ($this->getAttributesRaw()) {
            foreach ($this->getAttributesRaw() as $attrId => $value) {
                if (!isset($this->attributes[$attrId])) {
                    continue;
                }
                $attr = $this->attributes[$attrId];
                if (!$attr->isFilterable()) {
                    if (isset($this->attributesRaw[$attrId])) {
                        unset($this->attributesRaw[$attrId]);
                    }
                    continue;
                }
                if ($attr instanceof AttributeLocation) {
                    $filter = $this->getGeoLocationFilter($attr, $value);
                    if ($filter) {
                        $res[] = $filter;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function dateFormatter($value)
    {
        return (new \DateTime($value))->format(\DateTime::ATOM);
    }

    /**
     * @inheritdoc
     */
    public function addQueries(\Elastica\Query\Bool $query)
    {
        if ($this->getAttributesRaw()) {
            foreach ($this->getAttributesRaw() as $attrId => $value) {
                if (!isset($this->attributes[$attrId]) || $value === '') {
                    continue;
                }
                $attr = $this->attributes[$attrId];
                if (!$attr->isFilterable()) {
                    if (isset($this->attributesRaw[$attrId])) {
                        unset($this->attributesRaw[$attrId]);
                    }
                    continue;
                }
                $fieldName = 'eav_values.' . $attrId;
                if ($attr instanceof AttributeDate) {
                    $value = trim($value);
                    if ($range = $this->parseRange($value, [$this, 'dateFormatter'])) {
                        $query->addMust(
                            new \Elastica\Query\Range($fieldName, $range)
                        );
                        continue;
                    }
                    $value = $this->dateFormatter($value);
                } elseif ($attr instanceof AttributeBoolean) {
                    $value = boolval($value);
                } elseif ($attr instanceof AttributeInput || $attr instanceof AttributeTextarea) {
                    $tmp = new \Elastica\Query\MultiMatch();
                    $tmp->setFields($fieldName . '*');
                    $tmp->setType(\Elastica\Query\MultiMatch::TYPE_MOST_FIELDS);
                    $tmp->setQuery($value);

                    if ($this->findOptionTextareaHard && $attr instanceof AttributeTextarea) {
                        $tmp->setOperator('and');
                    } elseif ($this->findOptionInputHard && $attr instanceof AttributeInput) {
                        $tmp->setOperator('and');
                    }
                    $query->addMust($tmp);

                    $tmp = new \Elastica\Query\Term();
                    $tmp->setTerm($fieldName, $value, 3);
                    $query->addShould($tmp);
                    continue;
                } elseif ($attr instanceof AttributeNumeric) {
                    $value = trim($value);
                    if ($range = $this->parseRange($value)) {
                        $query->addMust(
                            new \Elastica\Query\Range($fieldName, $range)
                        );
                        continue;
                    }
                    $value = (float)$value;
                } elseif ($attr instanceof AttributeLocation) {
                    continue;//Filter
                }

                $query->addMust(
                    new \Elastica\Query\Term([$fieldName => $value,])
                );
            }
        }
    }

    protected function convertUsedAttributes()
    {
        if ($this->attributes && count($this->attributes) || !$this->getAttributesRaw()) {
            return;
        }
        $usedIds = [];
        foreach ($this->getAttributesRaw() as $attrId => $value) {
            $usedIds[] = $attrId;
        }

        $attributes = $this->getAvailableAttributes($usedIds);

        foreach ($attributes as $attribute) {
            $this->attributes[$attribute->getId()] = $attribute;
        }
    }

    /**
     * You should rewrite this method. This method return list of attributes - available filters; and for validation.
     *
     * @param array $usedIds attribute ids
     * @return \Brander\Bundle\EAVBundle\Entity\Attribute[]
     */
    protected function getAvailableAttributes(array $usedIds = [])
    {
        $qb = $this->getAttributeRepository()->createQueryBuilder('a');
        $qb->where('a.isSortable = 1 or a.isFilterable = 1');
        if (count($usedIds)) {
            $qb->andWhere('a.id in (:ids)')
               ->setParameter('ids', $usedIds);
        }
        /** @var Attribute[] $attributes */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return FilterableAttribute[]
     */
    public function getFilterableAttributes()
    {
        $res = [];
        $attributes = $this->getAvailableAttributes();
        foreach ($attributes as $attribute) {
            $item = new FilterableAttribute();
            $item->setIsSortable($attribute->isSortable())
                 ->setIsFilterable($attribute->isFilterable())
                 ->setField(['attributes', $attribute->getId()])
                 ->setView($attribute->getFilterType())
                 ->setViewOrder($attribute->getFilterOrder());
            $item->setAttribute($attribute);
            $res[] = $item;
        }
        return $res;
    }

    /**
     * @return void
     */
    public function prettify()
    {
        $this->convertUsedAttributes();
    }
}