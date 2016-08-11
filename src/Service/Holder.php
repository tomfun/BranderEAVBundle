<?php
namespace Brander\Bundle\EAVBundle\Service;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * Service for work with attributes
 *
 * @author Tomfun <tomfun1990@gmail.com>
 */
class Holder implements EventSubscriberInterface
{
    const VALUE_NAME = 'Brander\\Bundle\\EAVBundle\\Entity\\Value';

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            [
                'event'  => 'serializer.post_deserialize',
                'method' => 'onPostDeserialize',
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostDeserialize(ObjectEvent $event)
    {
        $object = $event->getObject();
        if ($object instanceof Attribute) {
            $this->setValueClass($object);
        }
    }

    /**
     * @param string $discriminator
     * @return Attribute
     */
    public function createFromShortName($discriminator)
    {
        $map = $this->getAttributeMap();
        if (!isset($map[$discriminator])) {
            throw new \InvalidArgumentException("Can't find class by this discriminator value: '$discriminator'");
        }

        return $this->createAttribute($map[$discriminator]);
    }

    /**
     * @param string $class
     * @return Attribute
     */
    public function createAttribute($class)
    {
        if ($class == Attribute::class) {
            throw new \InvalidArgumentException("Can't instantiate abstract class");
        }
        if (!is_subclass_of($class, Attribute::class)) {
            throw new \InvalidArgumentException("This class is not inherited from Attribute: '$class'");
        }
        $entity = new $class;
        if (!$entity instanceof Attribute) {
            throw new \RuntimeException("This class is not inherited from Attribute: $class");
        }
        $this->setValueClass($entity);

        return $entity;
    }

    /**
     * @param Attribute $entity
     * @return $this
     */
    public function setValueClass(Attribute $entity)
    {
        $metadataFactory = $this->manager->getMetadataFactory();
        /** @var ClassMetadata $metaDataAttribute */
        $metaDataAttribute = $metadataFactory->getMetadataFor(get_class($entity));
        /** @var ClassMetadata $metaDataEntity */
        $metaDataEntity = $metadataFactory->getMetadataFor(static::VALUE_NAME);
        $entity->setValueClass($metaDataEntity->discriminatorMap[$metaDataAttribute->discriminatorValue]);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAttributeMap()
    {
        $metadataFactory = $this->manager->getMetadataFactory();
        $metaDataValue = $metadataFactory->getMetadataFor(Attribute::class);
        /** @var ClassMetadata $metaDataValue */
        $list = $metaDataValue->discriminatorMap;

        return $list;
    }
}