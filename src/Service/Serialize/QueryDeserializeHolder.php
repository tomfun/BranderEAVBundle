<?php
namespace Brander\Bundle\EAVBundle\Service\Serialize;

use Brander\Bundle\EAVBundle\Entity as EAV;
use Brander\Bundle\EAVBundle\Model\Elastica\EavElasticaQuery;
use Doctrine\ORM\EntityRepository;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * Just add repository to query
 * @author Tomfun <tomfun1990@gmail.com>
 */
class QueryDeserializeHolder implements EventSubscriberInterface
{
    const VALUE_NAME = 'Brander\\Bundle\\EAVBundle\\Entity\\Value';

    /**
     * @var EntityRepository
     */
    private $repoAttribute;

    /**
     * @param EntityRepository $repoAttribute
     */
    public function __construct(EntityRepository $repoAttribute)
    {
        $this->repoAttribute = $repoAttribute;
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
        $this->initializeQuery($object);
    }

    /**
     * @param EavElasticaQuery $query
     * @return EavElasticaQuery
     */
    public function initializeQuery($query)
    {
        if ($query instanceof EavElasticaQuery) {
            $query->setAttributeRepository($this->repoAttribute);
        }
        return $query;
    }

    /**
     * @param string $class
     * @return EavElasticaQuery
     */
    public function createQuery($class = EavElasticaQuery::class)
    {
        if (!is_subclass_of($class, EavElasticaQuery::class) && $class !== EavElasticaQuery::class) {
            throw new \InvalidArgumentException("wrong class");
        }
        $query = new $class();
        return $this->initializeQuery($query);
    }
}