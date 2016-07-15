<?php
namespace Brander\Bundle\EAVBundle\Service\Elastica;

use Brander\Bundle\EAVBundle\Repo\Value;
use Werkint\Bundle\StatsBundle\Service\Provider\StatsProviderInterface;

/**
 * @author tomfun
 */
class ValueStatsProvider
{

    const VALUE_STAT = 'Brander\Bundle\EAVBundle\Repo\ValueMin';

    /**
     * @var Value
     */
    private $repoValue;

    /**
     * @param Value $value
     */
    public function __construct(
        Value $value
    ) {
        $this->repoValue = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getStat($name, array $options)
    {
        $attributeId = $options["attribute_id"];
        switch ($name) {
            case static::VALUE_STAT:
                return $this->repoValue->minMaxByAttributeId($attributeId);
        }

        throw new \Exception('Wrong attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function getStatCacheName($name, array $options)
    {
        switch ($name) {
            case static::VALUE_STAT:
                return static::VALUE_STAT . '_cached_' . $options["attribute_id"];
        }

        throw new \Exception('Wrong attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function isStatPublic($name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatsSupported()
    {
        return [
            static::VALUE_STAT,
        ];
    }

}