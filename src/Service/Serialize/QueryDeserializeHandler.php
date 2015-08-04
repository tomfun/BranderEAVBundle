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
class QueryDeserializeHandler implements EventSubscriberInterface
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
//todo: delete file, move this logic to EavList
    /**
     * @param ObjectEvent $event
     */
    public function onPostDeserialize(ObjectEvent $event)
    {
        $object = $event->getObject();
        if ($object instanceof EavElasticaQuery) {
            $object->setAttributeRepository($this->repoAttribute);
        }
    }
}