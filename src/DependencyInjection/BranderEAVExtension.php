<?php
namespace Brander\Bundle\EAVBundle\DependencyInjection;

use Brander\Bundle\EAVBundle\Model\Elastica\EavElasticaQuery;
use Brander\Bundle\EAVBundle\Model\Elastica\EavElasticaResult;
use Brander\Bundle\EAVBundle\Model\SearchableCustomMappingsInterface;
use Brander\Bundle\EAVBundle\Model\SearchableEntityInterface;
use Brander\Bundle\EAVBundle\Model\SearchableNeedIndexInterface;
use Brander\Bundle\EAVBundle\Model\SearchableNeedUpdateInterface;
use Brander\Bundle\EAVBundle\Service\Serialize\SimpleSerializer;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * Extension for BranderEAVBundle.
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 */
class BranderEAVExtension extends Extension implements PrependExtensionInterface
{
    const ELASTICA_SERIALIZER_HANDLER = 'brander_eav.extensible_entity.handler';
    const MODEL_SEARCHABLE_INTERFACE = SearchableEntityInterface::class;
    const MODEL_SEARCHABLE_NEED_INDEX_INTERFACE = SearchableNeedIndexInterface::class;
    const MODEL_SEARCHABLE_NEED_UPDATE_INTERFACE = SearchableNeedUpdateInterface::class;
    const ELASTICA_TEMPLATE_FILE = '/../Resources/config/data/elstica-index-example.yml';
    const JMS_SERIALIZER_CLASS = 'FOS\ElasticaBundle\Serializer\Callback';

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias(), $container->getParameter('locale'));
    }

    /**
     * Use
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $config = $container->getExtensionConfig($this->getAlias());
        $selfConfig = $this->load($config, $container);
        if (isset($bundles['FOSElasticaBundle']) && count($selfConfig['list_class_map'])) {
            $selfConfig['list_class_map'] = array_unique($selfConfig['list_class_map'], SORT_REGULAR);
            if (!count($selfConfig['list_class_map'])) {
                return;
            }

            $config = $container->getExtensionConfig('fos_elastica');
            if ($config) {
                $config = $config[0];
            }

            $newConfig = $this->makeElasticaConfig(
                $config,
                $selfConfig['list_class_map'],
                $container->getParameter('kernel.environment'),
                $selfConfig['useJmsSerializer']
            );
            foreach ($container->getExtensions() as $name => $extension) {
                switch ($name) {
                    case 'fos_elastica':
                        //insert configuration data for FOS Elastica Bundle
                        $container->prependExtensionConfig($name, $newConfig);
                        break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configDir = realpath(__DIR__.'/../Resources/config');

        $processor = new Processor();
        $config = $processor->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        $this->normalizeListClassMap($config['list_class_map']);

        $container->setParameter(
            $this->getAlias(),
            $config
        );
        $container->setParameter(
            $this->getAlias().'.config_directory',
            $configDir
        );
        $container->setParameter(
            $this->getAlias().'.locales_supported',
            $config['locales_supported']
        );

        // fixtures config
        if ($config['fixturesDirectory']) {
            $fixturesDir = rtrim($config['fixturesDirectory'], '\/');
            $fixturesDir = str_replace('%kernel.root_dir%', $container->getParameter('kernel.root_dir'), $fixturesDir);
            $fixturesDir = realpath($fixturesDir);
        } else {
            $fixturesDir = $configDir.'/data';
        }
        $container->setParameter(
            $this->getAlias().'.fixtures_directory',
            $fixturesDir
        );
        //old style
        $container->setParameter(
            $this->getAlias().'.jsmodeldir',
            realpath(__DIR__.'/../Resources/scripts/jsmodel')
        );
        //new style
        $container->setParameter(
            $this->getAlias().'.'.'frontend_config', //JsmodelProviderPass::PARAMETER_POSTFIX,
            [
                [
                    'path' => realpath(__DIR__.'/../Resources/scripts/jsmodel'),
                    'name' => 'brander-eav',
                ],
            ]
        );

        $container->setParameter($this->getAlias().'.manageRole', $config['manageRole']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator($configDir)
        );
        $loader->load('services.yml');
        $loader->load('doctrine.yml');
        $classes = $config['searchable'];
        foreach ($config['list_class_map'] as $classMap) {
            $classes[] = $classMap['entity'];
        }
        $classes = array_unique($classes);
        if (count($classes)) {
            $this->makeSerializeHandlers($classes, $container, $config['useJmsSerializer']);
        }


        return $config;
    }

    /**
     * @param array $listClassMap
     */
    public function normalizeListClassMap(array &$listClassMap)
    {
        foreach ($listClassMap as &$classMap) {
            $class = $classMap['entity'];
            if (!class_exists($class, true)) {
                throw new \InvalidArgumentException(
                    'in brander_eav.list_class_map.entity class: '.$class.' not found'
                );
            }
            if (!is_subclass_of($class, self::MODEL_SEARCHABLE_INTERFACE)) {
                throw new \InvalidArgumentException(
                    'in brander_eav.list_class_map.entity class: '
                    .$class
                    .' not implement '
                    .self::MODEL_SEARCHABLE_INTERFACE
                );
            }

            $lastNameCamel = substr($class, strrpos($class, '\\') + 1);
            $lastName = strtolower($lastNameCamel);
            $name = substr($class, 0, strpos($class, '\\')).'_'.$lastName;
            $name = strtolower($name);
            $classMap['lastName'] = $lastName;
            $classMap['name'] = $name;
            $classMap['finder'] = 'fos_elastica.finder.'.$name.'.'.$lastName;
            $classMap['list'] = 'brander_eav.model.elastica.list.'.$name.'.'.$lastName;

            $elasticaNameSpace = substr(
                $class,
                0,
                strrpos($class, '\\', -(strlen($lastNameCamel) + 2)) + 1
            ); //$lastNameCamel
            $elasticaNameSpace .= 'Model';

            $resultClass = $classMap['result'];
            if (!$resultClass) {
                $resultClass = $elasticaNameSpace.'\\'.$lastNameCamel.'Result';
            }
            if (!is_subclass_of($resultClass, EavElasticaResult::class) && $resultClass !== EavElasticaResult::class) {
                throw new \InvalidArgumentException(
                    "Class map element: '".$resultClass."' is not extended from  '".EavElasticaResult::class
                );
            }
            $classMap['result'] = $resultClass;

            $queryClass = $classMap['query'];
            if (!$queryClass) {
                $queryClass = $elasticaNameSpace.'\\'.$lastNameCamel.'Query';
            }
            if (!is_subclass_of($queryClass, EavElasticaQuery::class) && $queryClass !== EavElasticaQuery::class) {
                throw new \InvalidArgumentException(
                    "Class map key: '".$queryClass."' is not extended from  '".EavElasticaQuery::class
                );
            }
            $classMap['query'] = $queryClass;

        }
    }

    /**
     * Subscribe on serialize event
     *
     * @param array            $classes An array of searchable classes
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param bool             $useJmsSerializer
     */
    private function makeSerializeHandlers(array $classes, ContainerBuilder $container, $useJmsSerializer = true)
    {
        $serializeHandler = $container->getDefinition(self::ELASTICA_SERIALIZER_HANDLER);
        $serializeHandler->replaceArgument(0, $classes);
        if (!$useJmsSerializer) {
            return;
        }
        foreach ($classes as $class) {
            if (!class_exists($class, true)) {
                throw new \InvalidArgumentException('in brander_eav.searchable class: '.$class.' not found');
            }
            if (!is_subclass_of($class, self::MODEL_SEARCHABLE_INTERFACE)) {
                throw new \InvalidArgumentException(
                    'in brander_eav.searchable class: '.$class.' not implement '.self::MODEL_SEARCHABLE_INTERFACE
                );
            }
            $serializeHandler->addTag(
                "jms_serializer.handler",
                [
                    'type' => $class,
                    'format' => "json",
                    'method' => "serializeToJson",
                ]
            );
        }
    }

    /**
     * @param array  $config
     * @param array  $classMap
     * @param string $env
     * @param bool   $useJmsSerializer
     * @return array
     */
    private function makeElasticaConfig($config, $classMap, $env, $useJmsSerializer = true)
    {
        $newConfig = $config;
        if ($useJmsSerializer) {//default serialize callback
            if (!isset($newConfig['serializer']['callback_class'])) {
                $newConfig['serializer']['callback_class'] = self::JMS_SERIALIZER_CLASS;
            }
            if (!isset($newConfig['serializer']['serializer'])) {
                $newConfig['serializer']['serializer'] = 'serializer';
            }
        } else {
            if (!isset($newConfig['serializer']['calfllback_class'])) {
                $newConfig['serializer']['callback_class'] = SimpleSerializer::class;
            }
        }

        $template = Yaml::parse(file_get_contents(realpath(__DIR__.self::ELASTICA_TEMPLATE_FILE)));
        $template = $template['fos_elastica']['indexes']['namespace_entity'];

        foreach ($classMap as $entityConfig) {
            $class = $entityConfig['entity'];
            $name = $entityConfig['name'];
            $lastName = $entityConfig['lastName'];
            $index = $template;

            if (isset($config['indexes'][$name])) {
                continue;//if already present => skip !
            }

            $index['index_name'] = $name.'_'.$env;
            if (!is_subclass_of($class, self::MODEL_SEARCHABLE_NEED_INDEX_INTERFACE)) {
                unset($index['types']['entity']['indexable_callback']);
            }
            if (!is_subclass_of($class, self::MODEL_SEARCHABLE_NEED_UPDATE_INTERFACE)) {
                unset($index['types']['entity']['updatable_callback']);
            }
            if (is_subclass_of($class, SearchableCustomMappingsInterface::class)) {
                $method = SearchableCustomMappingsInterface::METHOD_NAME_MAPPINGS;
                $mappings = $class::$method();
                foreach ($mappings as $fieldName => $analyzer) {
                    if ($analyzer === SearchableCustomMappingsInterface::ELASTICA_MAPPING_GEO_POINT) {
                        $index['types']['entity']['mappings'][$fieldName] = [
                            'type' => 'geo_point',
                            'lat_lon' => 'true',
                        ];
                        continue;
                    }
                    if ($analyzer === SearchableCustomMappingsInterface::ELASTICA_MAPPING_NOT_ANALYZED) {
                        $index['types']['entity']['mappings'][$fieldName] = [
                            'index' => 'not_analyzed',
                        ];
                        continue;
                    }
                    $index['types']['entity']['mappings'][$fieldName] = [
                        'type' => 'string',
                        'analyzer' => $analyzer,
                    ];
                }
            }
            $index['types']['entity']['persistence']['model'] = $class;
            $index['types'] = [
                $lastName => $index['types']['entity'],
            ];
            $newConfig['indexes'][$name] = $index;
        }
        return $newConfig;
    }
}
