<?php
namespace Brander\Bundle\EAVBundle\Service\Serialize;

use Brander\Bundle\EAVBundle\Entity as EAV;
use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Model\SearchableCallableInterface;
use Brander\Bundle\EAVBundle\Model\SearchableEntityInterface;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Can serialize EAV models to JSON
 * @author Tomfun <tomfun1990@gmail.com>
 */
class SerializeHandler
{
    /** @var  string[] */
    private $supportedElasticaClasses;
    /** @var  string */
    private $locale;
    /** @var  EventDispatcherInterface */
    private $dispatcher;

    /**
     * @param array                    $classes elastica serialization classes
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $defaultLocale
     */
    public function __construct(array $classes, RequestStack $requestStack, EventDispatcherInterface $dispatcher = null, $defaultLocale = 'en')
    {
        $this->supportedElasticaClasses = $classes;
        $this->locale = $defaultLocale;
        $this->dispatcher = $dispatcher;
        if ($request = $requestStack->getCurrentRequest()) {
            $this->locale = $request->getLocale();
        }
    }

    /**
     * @param $class
     * @return bool
     */
    public function supportClass($class)
    {
        return in_array($class, $this->supportedElasticaClasses);
    }

    /**
     * @param SearchableEntityInterface $object
     * @return string
     */
    public function serialize(SearchableEntityInterface $object)
    {
        return json_encode($this->collectData($object));
    }

    /**
     * @Serializer\HandlerCallback("json", direction="serialization")
     * @param JsonSerializationVisitor  $visitor
     * @param SearchableEntityInterface $data
     * @param array                     $type
     * @param SerializationContext      $context
     * @return \ArrayObject|mixed
     * @throws \Exception
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        SearchableEntityInterface $data = null,
        array $type = [],
        SerializationContext $context = null
    )
    {//$visitor, $data, $type, $context
        /** @var $metadata ClassMetadata */
        $metadata = $context->getMetadataFactory()->getMetadataForClass($type['name']);
        if (
            !$context->attributes->get('groups') instanceof \PhpOption\None
            && in_array('elastica', $context->attributes->get('groups')->get())
        ) {
            $visitor->startVisitingObject($metadata, $this, $type, $context);
            foreach ($this->collectData($data) as $index => $data) {
                $visitor->addData($index, $data);
            }

            return $visitor->endVisitingObject($metadata, $this, $type, $context);
        }

        return null; // TODO
        return $this->defaultSerializerBehavior($visitor, $data, $metadata, $type, $context);
    }

    /**
     * @param SearchableEntityInterface $data
     * @return array
     * @throws \Exception
     */
    protected function collectData(SearchableEntityInterface $data)
    {
        $attrs = [];
        if ($data->getAttributeSet() instanceof EAV\AttributeSet) {
            $should = $data->getAttributeSet()->getAttributes();
        } else {
            throw new \Exception("getAttributeSet must return AttributeSet instance");
        }
        $shouldIds = [];
        $should->forAll(
            function ($key, Attribute $attribute) use (&$shouldIds) {
                if ($attribute->isFilterable() || $attribute->isSortable()) {
                    $shouldIds[] = $attribute->getId();
                }

                return true;
            }
        );
        $locale = $this->locale;
        if (method_exists($data, 'getCurrentLocale')) {
            try {
                $locale = $data->getCurrentLocale();
            } catch (\Exception $e) {

            }
        }

        foreach ($data->getValues() as $value) {
            $attrId = $value->getAttribute()->getId();
            if (!in_array($attrId, $shouldIds)) {
                continue;
            }
            if ($value instanceof EAV\ValueDate) {
                $attrs[$attrId] = $value->getValue()->format(\DateTime::ATOM);
            } elseif ($value instanceof EAV\ValueNumeric) {
                $attrs[$attrId] = floatval($value->getValue());
            } elseif ($value instanceof EAV\ValueBoolean) {
                $attrs[$attrId] = !!$value->getValue();
            } elseif ($value instanceof EAV\ValueLocation) {
                $attrs[$attrId.EAV\ValueLocation::ELASTICA_POSTFIX] = [
                    'lat' => $value->getValue()->getLat(),
                    'lon' => $value->getValue()->getLon(),
                ];
            } elseif ($value instanceof EAV\ValueTextarea || $value instanceof EAV\ValueInput) {
                $attrs[$attrId] = $value->getValue();
                if ($locale === 'ru') {
                    $attrId .= EAV\ValueTextarea::ELASTICA_POSTFIX_RU;
                } elseif ($locale === 'en') {
                    $attrId .= EAV\ValueTextarea::ELASTICA_POSTFIX_EN;
                } elseif ($locale === 'es') {
                    $attrId .= EAV\ValueTextarea::ELASTICA_POSTFIX_ES;
                } elseif ($locale === 'fr') {
                    $attrId .= EAV\ValueTextarea::ELASTICA_POSTFIX_FR;
                }
                $attrs[$attrId] = $value->getValue();
            } else {
                $attrs[$attrId] = $value->getValueRaw();
            }
        }

        $result = ['eav_values' => $attrs];
        if ($data instanceof SearchableCallableInterface) {
            $additional = $data->getAdditionalElasticaData();
            $result = array_merge($result, $additional);
        }

        return $result;
    }


    /**
     * public function accept($data, array $type = null, Context $context)
     *
     * @param JsonSerializationVisitor       $visitor
     * @param SearchableEntityInterface|null $data
     * @param ClassMetadata                  $metadata
     * @param array                          $type
     * @param SerializationContext|null      $context
     * @return null
     */
    protected function defaultSerializerBehavior(JsonSerializationVisitor $visitor, SearchableEntityInterface $data = null, ClassMetadata $metadata, array $type = [], SerializationContext $context = null)
    {

        $exclusionStrategy = $context->getExclusionStrategy();

        //    /** @var $metadata ClassMetadata */
        //$metadata = $this->metadataFactory->getMetadataForClass($type['name']);

        if (null !== $exclusionStrategy && $exclusionStrategy->shouldSkipClass($metadata, $context)) {
//            $this->leaveScope($context, $data);

            return null;
        }

        $context->pushClassMetadata($metadata);

        foreach ($metadata->preSerializeMethods as $method) {
            $method->invoke($data);
        }

        $object = $data;
//
//        if (isset($metadata->handlerCallbacks[$context->getDirection()][$context->getFormat()])) {
//            $rs = $object->{$metadata->handlerCallbacks[$context->getDirection()][$context->getFormat()]}(
//                $visitor,
//                $context instanceof SerializationContext ? null : $data,
//                $context
//            );
//            $this->afterVisitingObject($metadata, $object, $type, $context);
//
//            return $context instanceof SerializationContext ? $rs : $object;
//        }

        $visitor->startVisitingObject($metadata, $object, $type, $context);
        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            if (null !== $exclusionStrategy && $exclusionStrategy->shouldSkipProperty($propertyMetadata, $context)) {
                continue;
            }

            $context->pushPropertyMetadata($propertyMetadata);
            $visitor->visitProperty($propertyMetadata, $data, $context);
            $context->popPropertyMetadata();
        }

//        $context->stopVisiting($data);
        $context->popClassMetadata();

        foreach ($metadata->postSerializeMethods as $method) {
            $method->invoke($object);
        }

        if (null !== $this->dispatcher && $this->dispatcher->hasListeners('serializer.post_serialize', $metadata->name, $context->getFormat())) {
            $this->dispatcher->dispatch('serializer.post_serialize', $metadata->name, $context->getFormat(), new ObjectEvent($context, $object, $type));
        }

    }
}