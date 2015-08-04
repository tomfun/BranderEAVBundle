<?php
namespace Brander\Bundle\EAVBundle\EventListener;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\ValueSelect;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM;

/**
 * Слушатель событий, подставляет нужные вещи.
 * Загружает в значение типа селект, соответствующую опцию.
 * А в аттрибут, подставляет соответствующий класс значения.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ValueSelectListener implements
    EventSubscriber
{
    const ATTRIBUTE_NAME = 'Brander\\Bundle\\EAVBundle\\Entity\\Attribute';
    const OPTION_NAME = 'Brander\\Bundle\\EAVBundle\\Entity\\AttributeSelectOption';
    const VALUE_NAME = 'Brander\\Bundle\\EAVBundle\\Entity\\Value';

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            ORM\Events::postPersist,
            ORM\Events::postLoad,
        ];
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function postLoad(ORM\Event\LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ValueSelect) {

            $repo = $args->getEntityManager()->getRepository(static::OPTION_NAME);
            if ($entity->getValue()) {
                $option = $repo->find($entity->getValue());
                $entity->setOption($option);
            }
        }

        if ($entity instanceof Attribute) {
            $metadataFactory = $args->getEntityManager()->getMetadataFactory();
            $metaDataAttribute = $metadataFactory->getMetadataFor(get_class($entity));
            $metaDataEntity = $metadataFactory->getMetadataFor(static::VALUE_NAME);
            $entity->setValueClass($metaDataEntity->discriminatorMap[$metaDataAttribute->discriminatorValue]);
        }
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(ORM\Event\LifecycleEventArgs $args)
    {
        $this->postLoad($args);
    }
}