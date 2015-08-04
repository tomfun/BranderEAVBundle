<?php
namespace Brander\Bundle\EAVBundle\Service\Security;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\AttributeGroup;
use Brander\Bundle\EAVBundle\Entity\AttributeSet;
use Brander\Bundle\EAVBundle\Entity\Value;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter for managing attributes, values, other
 * @author Tomfun <tomfun1990@gmail.com>
 */
class UniversalManageVoter extends AbstractVoter
{
    const CREATE = 'create';
    const VIEW = 'view';
    const DELETE = 'delete';
    const UPDATE = 'update';
    const MANAGE = 'manage';
    /**
     * @var string
     */
    private $role;

    /**
     * @param $role
     */
    public function __construct($role)
    {
        $this->role = $role;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array_merge($this->getSupportedCollectionClasses(), [FakeCollection::class,]);
    }

    /**
     * Used as field in FakeCollection class
     * @return array
     */
    protected function getSupportedCollectionClasses()
    {
        return [
            Attribute::class,
            AttributeSet::class,
            AttributeGroup::class,
            Value::class,
        ];
    }

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute
     *
     * @return array an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    protected function getSupportedAttributes()
    {
        RETURN [
            self::CREATE,
            self::VIEW,
            self::UPDATE,
            self::DELETE,
            self::MANAGE,
        ];
    }

    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user)
     *
     * @param string $attribute
     * @param object $object
     * @param UserInterface|string $user
     *
     * @return bool
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if ($object instanceof FakeCollection) {
            if (!$this->supportsClass($object->getCollectionClass())) {
                return self::ACCESS_ABSTAIN;
            }
        }
        if ($object instanceof AttributeGroup && $attribute === self::VIEW) {
            return self::ACCESS_GRANTED;
        }
        if ($object instanceof AttributeSet && $attribute === self::VIEW) {
            return self::ACCESS_GRANTED;
        }
        if ($object instanceof Attribute && $attribute === self::VIEW) {
            return self::ACCESS_GRANTED;
        }
        return in_array($this->role, $user->getRoles()) ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }
}