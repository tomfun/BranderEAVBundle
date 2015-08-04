<?php
namespace Brander\Bundle\EAVBundle\Service\Security;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
class FakeCollection
{
    /** @var string */
    private $collectionClass;

    /**
     * @param string $collectionClass
     */
    public function __construct($collectionClass)
    {
        $this->collectionClass = $collectionClass;
    }

    /**
     * @return string
     */
    public function getCollectionClass()
    {
        return $this->collectionClass;
    }

}