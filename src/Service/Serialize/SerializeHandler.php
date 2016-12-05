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
 * Can serialize EAV models to JSON for Elastic
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
     * Not used
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

        return null;
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
            } elseif ($value instanceof EAV\ValueMultiSelect) {
                $attrs[$attrId] = explode(',', $value->getValue());
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
}
