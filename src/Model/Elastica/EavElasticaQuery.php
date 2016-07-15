<?php
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
use Brander\Bundle\EAVBundle\Service\Elastica\ValueStatsProvider;
use Brander\Bundle\EAVBundle\Service\Filter\FilterProvider;
use Brander\Bundle\ElasticaSkeletonBundle\Entity\Aggregation;
use Brander\Bundle\ElasticaSkeletonBundle\Service\Elastica\ElasticaQuery;
use Doctrine\ORM\EntityRepository;
use Elastica\Query\BoolQuery;
use JMS\Serializer\Annotation as Serializer;
use Werkint\Bundle\StatsBundle\Service\StatsDirectorInterface;

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
     *  "2": "2003-06-15"// 2 - AttributeDate, but this is bad idea (this not the range as above, this exact field
     * search)
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
     * @var StatsDirectorInterface
     */
    private $stats;

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
     * @return StatsDirectorInterface
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param StatsDirectorInterface $stats
     *
     * @return $this
     */
    public function setStats(StatsDirectorInterface $stats)
    {
        $this->stats = $stats;
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
     * Add just geo distance filters and range filters
     * @return \Elastica\Filter\AbstractFilter[]
     */
    public function getFilters()
    {
        $res = [];
        if ($this->getAttributesRaw()) {
            foreach ($this->getAttributesRaw() as $attrId => $value) {
                if (!($attr = $this->needAddToFilter($attrId, $value))) {
                    continue;
                }
                $fieldName = 'eav_values.'.$attrId;
                if ($attr instanceof AttributeLocation) {
                    $filter = $this->getGeoLocationFilter($attr, $value);
                    if ($filter) {
                        $res[] = $filter;
                    }
                } elseif ($attr instanceof AttributeNumeric) {
                    if ($range = $this->parseRange($value, 'floatval')) {
                        $filter = new \Elastica\Filter\NumericRange($fieldName, $range);
                        $res[] = $filter;
                    }
                } elseif ($attr instanceof AttributeDate) {
                    $value = trim($value);
                    if ($range = $this->parseRange($value, [$this, 'dateFormatter'])) {
                        $filter = new \Elastica\Filter\Range($fieldName, $range);
                        $res[] = $filter;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * @inheritdoc
     */
    public function addQueries(BoolQuery $query)
    {
        if ($this->getAttributesRaw()) {
            foreach ($this->getAttributesRaw() as $attrId => $value) {
                if (!($attr = $this->needAddToQuery($attrId, $value))) {
                    continue;
                }
                $fieldName = 'eav_values.'.$attrId;
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
                    $tmp->setFields($fieldName.'*');
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
                    $value = (float) $value;
                } elseif ($attr instanceof AttributeLocation) {
                    continue;//Filter
                }

                $query->addMust(
                    new \Elastica\Query\Term([$fieldName => $value,])
                );
            }
        }
    }

    /**
     * @return array|Aggregation[]
     */
    public function getAggregations()
    {
        $res = [];
        $filterables = $this->getFilterableAttributes();
        foreach ($filterables as $filterable) {
            $fieldName = '';
            $aggregationType = $this->needAddToAggregation(
                $filterable->getField(),
                $filterable->getView(),
                $fieldName
            );
            if (!$aggregationType) {
                continue;
            }
            $res = array_merge($res, $this->getAutoAggregation($filterable->getField(), $fieldName, $aggregationType));
        }
        return $res;
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
        $this->convertEavOrder();
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
     * @param mixed             $value
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
                    $fieldName = $attr->getId().ValueLocation::ELASTICA_POSTFIX;
                    $geoQuery = new \Elastica\Filter\GeoDistance($fieldName, (string) $value, $distance);
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
     * @param string        $value
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
        foreach ($keywords as $keyword) {
            $format = $keyword.':\s*(.+?)\s*;';
            preg_match('/'.$format.'/i', $value, $res);
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
     * @param $attrId
     * @param $value
     * @return false|Attribute
     */
    protected function needAddToFilter($attrId, $value)
    {
        if (!isset($this->attributes[$attrId]) || $value === '') {
            return false;
        }
        $attr = $this->attributes[$attrId];
        if (!$attr->isFilterable()) {
            if (isset($this->attributesRaw[$attrId])) {
                unset($this->attributesRaw[$attrId]);
            }

            return false;
        }
        if ($attr instanceof AttributeLocation) {
            return $attr;
        }
        $whiteList = [FilterProvider::RANGE_FILTER_CUSTOM, FilterProvider::RANGE_FILTER_SIMPLE];
        $need = ($attr instanceof AttributeNumeric || $attr instanceof AttributeDate)
            && in_array($attr->getFilterType(), $whiteList);
        if ($need) {
            return $attr;
        }

        return false;
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
     * @param $attrId
     * @param $value
     * @return false|Attribute
     */
    protected function needAddToQuery($attrId, $value)
    {
        if (!isset($this->attributes[$attrId]) || $value === '') {
            return false;
        }
        $attr = $this->attributes[$attrId];
        if (!$attr->isFilterable()) {
            if (isset($this->attributesRaw[$attrId])) {
                unset($this->attributesRaw[$attrId]);
            }
            return false;
        }
        if ($attr instanceof AttributeLocation) {
            return false;
        }
        if (
            in_array($attr->getFilterType(), [FilterProvider::RANGE_FILTER_CUSTOM, FilterProvider::RANGE_FILTER_SIMPLE])
            && ($attr instanceof AttributeNumeric || $attr instanceof AttributeDate)
        ) {
            return false;
        }
        return $attr;
    }

    /**
     * @param string[] $fieldName
     * @param string   $jsView
     * @param string   $indexName - return
     * @return string|false - type
     */
    protected function needAddToAggregation(array $fieldName, $jsView, &$indexName)
    {
        if (count($fieldName) === 2 && $fieldName[0] === 'attributes' && is_numeric($attrId = $fieldName[1])) {
            $indexName = 'eav_values.'.$attrId;
            if ($jsView === FilterProvider::RANGE_FILTER_CUSTOM) {
                return 'range_basket';
            }
            if ($jsView === FilterProvider::RANGE_FILTER_SIMPLE) {
                return 'range';
            }
        }
        return false;
    }

    /**
     * @param string[] $fieldName
     * @param string   $indexName
     * @return Aggregation[]
     */
    protected function getAutoAggregationRange(array $fieldName, $indexName)
    {
        return [
            new Aggregation(
                $fieldName.'_max',
                'max',
                $indexName,
                'setAutoAggregation',
                ['type' => 'range', 'serializeName' => $fieldName,]
            ),
            new Aggregation(
                $fieldName.'_min',
                'min',
                $indexName,
                'setAutoAggregation',
                ['type' => 'range', 'serializeName' => $fieldName,]
            ),
        ];
    }

    /**
     * @param            $arr
     * @param            $interval
     * @param int        $size
     * @param bool|float $rounding
     */
    protected function calculateIntervalByStats($arr, &$interval, $size = 10, $rounding = false)
    {
        if ($arr) {
            $min = floor($arr['min']);
            $max = ceil($arr['max']);
        } else {
            $max = $min = 0;
        }
        if ($max > $min) {
            $interval = ($max - $min) / $size;
        }
        if ($rounding !== false) {
            $digits = log10($interval);
            $digits = round($digits);
            $rounding = pow(10, $digits - 2) * 25;
            $interval = round($interval / $rounding) * $rounding;
        }
    }

    /**
     * @param string[] $fieldName
     * @param string   $indexName
     * @param array    $options
     * @return Aggregation[]
     */
    protected function getAutoAggregationHistogram(array $fieldName, $indexName, array $options = [])
    {
        $fieldName = implode('.', $fieldName);
        if (isset($options['interval'])) {
            $interval = $options['interval'];
            $type = $options['type'];
            if (!in_array($type, ['histogram', 'dateHistogram'])) {
                throw new \InvalidArgumentException(
                    "empty or wrong option['type']. You must set interval with type."
                );
            }
        } else {
            $interval = 100 * 1000 * 1000;
            $size = isset($options['size']) ?: 10;
            if (!isset($options['attribute_id'])) {
                throw new \InvalidArgumentException(
                    "empty option['attribute_id']. You must set interval or attribute_id."
                );
            }

            /** @var Attribute $attr */
            $attr = $this->getAttributeRepository()->findOneBy(['id' => $options['attribute_id']]);
            $arr = $this->stats->getStat(ValueStatsProvider::VALUE_STAT, $options);
            $rounding = isset($options['rounding']) ? (float) $options['rounding'] : false;
            $this->calculateIntervalByStats($arr, $interval, $size, $rounding);
            if ($attr instanceof AttributeDate) {
                $interval .= 's';
            }
            $type = $attr instanceof AttributeDate ? 'dateHistogram' : 'histogram';
        }
        return [
            new Aggregation(
                $fieldName.'_histogram',
                $type,
                $indexName,
                'setAutoAggregation',
                [
                    'type' => 'range_basket',
                    'constructArguments' => [
                        $fieldName.'_histogram',//name
                        $indexName,//elastica field name
                        $interval,
                    ],
                    'extractValueField' => 'buckets',
                    'serializeName' => $fieldName,
                ]
            ),
        ];
    }

    /**
     * @param string[] $fieldName
     * @param string   $indexName
     * @param string   $aggregationType
     * @return Aggregation[]
     */
    protected function getAutoAggregation(array $fieldName, $indexName, $aggregationType)
    {
        $attrId = $fieldName[1];
        switch ($aggregationType) {
            case 'range':
                return $this->getAutoAggregationRange($fieldName, $indexName);
                break;
            case 'range_basket':
                return $this->getAutoAggregationHistogram($fieldName, $indexName, ['attribute_id' => $attrId]);
                break;
        }
        return [];
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

    protected function convertEavOrder()
    {
        $order = $this->getOrder();
        if ($order && count($order)) {
            $this->order[0] = preg_replace('/^(attributes)\.(\d+)$/', 'eav_values.$2', $order[0]);
        }
    }
}