<?php
namespace Brander\Bundle\EAVBundle\DependencyInjection\Compiler;

use Brander\Bundle\EAVBundle\Service\Elastica\EavList;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collect elastica finders and push this in listing service
 * @author tomfun
 */
class ElasticEavListCompilerPass implements
    CompilerPassInterface
{
    const ELASTICA_LIST_SERVICE = 'brander.bundle.elasticaskeleton.list';
    const QUERY_HOLDER_SERVICE = 'brander_eav.attribute.serialize.query_holder';
    const ELASTICA_LIST_EXPORT_SERVICE = 'brander_eav.elastica.list';
    const ELASTICA_LIST_CLASS_MAP = 'list_class_map';
    const ELASTICA_EXTENSION = 'brander_eav';

    /**
     * @param ContainerBuilder $container
     * @param array $classMap
     */
    public function makeElasticaList(ContainerBuilder $container, $classMap)
    {
        foreach ($classMap as $entityConfig) {
            $finder = $entityConfig['finder'];
            $name = $entityConfig['name'];
            $lastName = $entityConfig['lastName'];

            $indexDef = clone $container->getDefinition(self::ELASTICA_LIST_SERVICE);

            $indexId = self::ELASTICA_LIST_EXPORT_SERVICE . '.' . $name . '.' . $lastName;

            $indexDef->setArguments([new Reference($finder)]);
            $indexDef->setPublic(true);
            $indexDef->setAbstract(false);
            $indexDef->setClass($entityConfig['serviceClass'] ?: EavList::class);
            $indexDef->addMethodCall('setResultClass', [$entityConfig['result']]);
            $indexDef->addMethodCall('setQueryClass', [$entityConfig['query']]);
            $indexDef->addMethodCall('setQueryHolder', [new Reference(self::QUERY_HOLDER_SERVICE)]);

            $container->setDefinition($indexId, $indexDef);
        }
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(static::ELASTICA_LIST_SERVICE)
            || !$container->hasParameter(static::ELASTICA_EXTENSION)
        ) {
            return;
        }
        $extensionConfig = $container->getParameter(static::ELASTICA_EXTENSION);
        if (
            !isset($extensionConfig[static::ELASTICA_LIST_CLASS_MAP])
            || !count($extensionConfig[static::ELASTICA_LIST_CLASS_MAP])
        ) {
            return;
        }
        $this->makeElasticaList($container, $extensionConfig[static::ELASTICA_LIST_CLASS_MAP]);
    }
}
