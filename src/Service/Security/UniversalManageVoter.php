<?php
namespace Brander\Bundle\EAVBundle\Service\Security;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\AttributeGroup;
use Brander\Bundle\EAVBundle\Entity\AttributeSet;
use Brander\Bundle\EAVBundle\Entity\Value;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter for managing attributes, values, other
 * @author Tomfun <tomfun1990@gmail.com>
 */
class UniversalManageVoter implements VoterInterface
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
     * @param string $role access role
     */
    public function __construct($role)
    {
        $this->role = $role;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, $this->getSupportedAttributes());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        foreach ($this->getSupportedClasses() as $supportedClass) {
            if ($supportedClass === $class || is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Iteratively check all given attributes by calling isGranted
     *
     * This method terminates as soon as it is able to return ACCESS_GRANTED
     * If at least one attribute is supported, but access not granted, then ACCESS_DENIED is returned
     * Otherwise it will return ACCESS_ABSTAIN
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param object         $object The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object || !$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if ($this->isGranted($attribute, $object, $token->getUser())) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array_merge($this->getSupportedCollectionClasses(), [FakeCollection::class]);
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
        return [
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
     * @param string               $attribute
     * @param object               $object
     * @param UserInterface|string $user
     *
     * @return bool
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if ($object instanceof FakeCollection) {
            if (!$this->supportsClass($object->getCollectionClass())) {
                return false;
            }
        }
        if ($object instanceof AttributeGroup && $attribute === self::VIEW) {
            return true;
        }
        if ($object instanceof AttributeSet && $attribute === self::VIEW) {
            return true;
        }
        if ($object instanceof Attribute && $attribute === self::VIEW) {
            return true;
        }
        if ($user) {
            if ($user === 'anon.' && $this->role === $user) {
                return true;
            } elseif (method_exists($user, 'getRoles')) {
                return in_array($this->role, $user->getRoles());
            }
        }
        return false;
    }
}