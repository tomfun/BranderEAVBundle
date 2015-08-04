<?php
namespace Brander\Bundle\EAVBundle\Service\Serialize;

use FOS\ElasticaBundle\Serializer\Callback;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * elastica serializer
 * @author Tomfun <tomfun1990@gmail.com>
 */
class SimpleSerializer extends Callback implements ContainerAwareInterface
{
    /** @var SerializeHandler */
    protected $eavSerializer;

    /**
     * @param SerializeHandler $converter
     */
    protected function setEavSerializer(SerializeHandler $converter)
    {
        $this->eavSerializer = $converter;
    }

    /**
     * for EAV models use our serializer, or parent realization for others
     * @param $object
     * @return string
     */
    public function serialize($object)
    {
        if ($this->eavSerializer->supportClass(get_class($object))) {
            return $this->eavSerializer->serialize($object);
        }
        return parent::serialize($object);
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->setEavSerializer($container->get('brander_eav.extensible_entity.handler'));
    }
}