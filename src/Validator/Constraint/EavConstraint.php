<?php
namespace Brander\Bundle\EAVBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

/**
 * Constraint for Entity with EAV validator.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class EavConstraint extends Constraint
{
    public $messageExcess = 'Value %title% is excess';
    public $messageRequired = 'Value %title% is required';
    public $service = 'validator.eav_value';
    public $em = null;
    public $errorPath = null;
    public $ignoreNull = true;

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getDefaultOption()
    {
        return 'messageRequired';
    }
}
