<?php
namespace Brander\Bundle\EAVBundle\Validator\Constraint;

use Brander\Bundle\EAVBundle\Entity\AbstractTranslation;
use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\Value;
use Brander\Bundle\EAVBundle\Model\ExtensibleEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author mom <alinyonish@gmail.com>
 */
class EavConstraintValidation extends ConstraintValidator
{
    /**
     * @var string
     */
    private $locale;

    /**
     * EavConstraintValidation constructor.
     * @param string       $locale
     * @param RequestStack $requestStack
     */
    public function __construct($locale, RequestStack $requestStack)
    {
        $this->locale = $locale;
        if ($request = $requestStack->getCurrentRequest()) {
            $this->locale = $request->getLocale();
        }
    }

    /**
     * @param ExtensibleEntityInterface $entity
     * @param EavConstraint|Constraint  $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!($entity instanceof ExtensibleEntityInterface)) {
            $this->context->buildViolation('Value must be instance of ExtensibleEntityInterface')->addViolation();

            return;
        }
        $attributes = $entity->getAttributeSet()->getAttributes();
        if (!($attributes instanceof Collection)) {
            $attributes = new ArrayCollection($attributes);
        }
        $entityAttributes = $this->getAttributesFromValues($entity->getValues());
        if (!($entityAttributes instanceof Collection)) {
            $entityAttributes = new ArrayCollection($entityAttributes);
        }
        $this->containsLeftInRight($entityAttributes, $attributes, $constraint->messageExcess);

        $requiredAttributes = $attributes->filter(function (Attribute $attribute) {
            return $attribute->isRequired();
        });
        $this->containsLeftInRight($requiredAttributes, $entityAttributes, $constraint->messageRequired);
        $this->checkRequiredValues($entity, $constraint->messageRequired);
    }

    /**
     * @param Attribute[]|Collection $left
     * @param Attribute[]|Collection $right
     * @param string                 $message
     */
    private function containsLeftInRight(Collection $left, Collection $right, $message)
    {
        foreach ($left as $attr) {
            if (!$right->contains($attr)) {
                $this->buildViolation($attr, $message, 'values[]');
            }
        }
    }

    /**
     * @param Value[] $values
     * @return Attribute[]
     */
    private function getAttributesFromValues($values)
    {
        $result = [];
        foreach ($values as $val) {
            $result[] = $val->getAttribute();
        }

        return $result;
    }

    /**
     * @param ExtensibleEntityInterface $entity
     * @param string  $message
     */
    protected function checkRequiredValues(ExtensibleEntityInterface $entity, $message)
    {
        foreach ($entity->getValues() as $pathCounter => $val) {
            $attr = $val->getAttribute();
            if ($attr->isRequired() && !$val->isValid()) {
                $this->buildViolation($attr, $message, "values[$pathCounter].value");
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @param string    $message
     * @param string    $path
     */
    protected function buildViolation(Attribute $attribute, $message, $path = '')
    {
        $trans = $attribute->getTranslationsByLocale();
        $trans = isset($trans[$this->locale]) ? $trans[$this->locale] : array_pop($trans);
        if (!$trans) {
            $trans = '#'.$attribute->getId();
        }
        /** @var AbstractTranslation $trans */
        $title = $trans->getTitle();
        $violation = $this->context->buildViolation($message);
        if ($path) {
            $violation
                ->atPath($path);
        }
        $violation
            ->setParameter('%title%', $title)
            ->addViolation();
    }
}
