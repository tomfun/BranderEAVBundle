<?php
namespace Brander\Bundle\EAVBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collect filter js view name
 * @author tomfun
 */
class FilterModelCompilerPass implements
    CompilerPassInterface
{
    const EAV_FILTER_HOLDER_SERVICE = 'brander_eav.filter.holder';
    const JS_MODEL_SERVICE_TAG = 'brander_eav.filter.provider';

    /**
     * @param ContainerBuilder $container
     */
    public function makeCollectViews(ContainerBuilder $container)
    {
        if (!$container->has(self::EAV_FILTER_HOLDER_SERVICE)) {
            return;
        }
        $holder = $container->getDefinition(self::EAV_FILTER_HOLDER_SERVICE);
        foreach ($container->findTaggedServiceIds(self::JS_MODEL_SERVICE_TAG) as $providerName => $tags) {
            $holder->addMethodCall('addProvider', [new Reference($providerName)]);
        }
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $this->makeCollectViews($container);
    }
}
