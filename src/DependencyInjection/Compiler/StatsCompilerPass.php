<?php
namespace Brander\Bundle\EAVBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collect filter js view name
 * @author tomfun
 */
class StatsCompilerPass implements
    CompilerPassInterface
{
    const STATS_HOLDER_SERVICE = 'brander_eav.stats.stats_holder';
    const STATS_PROVIDER_TAG = 'brander_eav.filter.provider';

    /**
     * @param ContainerBuilder $container
     */
    public function makeCollectStats(ContainerBuilder $container)
    {
        if (!$container->has(self::STATS_HOLDER_SERVICE)) {
            return;
        }
        $holder = $container->getDefinition(self::STATS_HOLDER_SERVICE);
        foreach ($container->findTaggedServiceIds(self::STATS_PROVIDER_TAG) as $providerName => $tags) {
            $holder->addMethodCall('addProvider', [new Reference($providerName)]);
        }
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $this->makeCollectStats($container);
    }
}
