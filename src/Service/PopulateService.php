<?php
namespace Brander\Bundle\EAVBundle\Service;

use FOS\ElasticaBundle\Event\IndexPopulateEvent;
use FOS\ElasticaBundle\Event\TypePopulateEvent;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Provider\ProviderRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author mom <alinyonish@gmail.com>
 */
class PopulateService
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var ProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * @var array
     */
    private $listClassMap;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param ProviderRegistry         $providerRegistry
     * @param IndexManager             $indexManager
     * @param Resetter                 $resetter
     * @param array                    $listClassMap
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ProviderRegistry $providerRegistry,
        IndexManager $indexManager,
        Resetter $resetter,
        $listClassMap
    ) {
        $this->dispatcher = $dispatcher;
        $this->providerRegistry = $providerRegistry;
        $this->indexManager = $indexManager;
        $this->resetter = $resetter;
        $this->listClassMap = $listClassMap;
    }


    /**
     * @param array $options
     */
    public function reindex($options = [])
    {
        foreach ($this->listClassMap as $classMap) {
            $name = $classMap['name'];
            $type = $classMap['lastName'];
            $event = new IndexPopulateEvent($name, true, $options);
            $this->dispatcher->dispatch(IndexPopulateEvent::PRE_INDEX_POPULATE, $event);
            $this->resetter->resetIndex($name, true);
            $this->reindexType($name, $type, $options);
            $this->dispatcher->dispatch(IndexPopulateEvent::POST_INDEX_POPULATE, $event);
            $this->indexManager->getIndex($name)->refresh();
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     */
    private function reindexType($name, $type, $options)
    {
        $event = new TypePopulateEvent($name, $type, true, $options);
        $this->dispatcher->dispatch(TypePopulateEvent::PRE_TYPE_POPULATE, $event);
        $provider = $this->providerRegistry->getProvider($name, $type);
        $provider->populate(null, $event->getOptions());
        $this->dispatcher->dispatch(TypePopulateEvent::POST_TYPE_POPULATE, $event);
        $this->indexManager->getIndex($name)->refresh();
    }
}
