<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Brander\Bundle\EAVBundle\Entity\AttributeSet;
use Brander\Bundle\EAVBundle\Service\Security\UniversalManageVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Manage attribute set via REST
 * @author tomfun
 * @Rest\Route("/eav/rest-set")
 */
class RestSetController
{
    use ValidationTrait;

    /**
     * @var SerializerInterface
     * @DI\Inject("serializer")
     */
    private $serializer;

    /**
     * @var AuthorizationCheckerInterface
     *
     * @DI\Inject("security.authorization_checker")
     */
    private $securityChecker;

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\AttributeSet"
     * )
     *
     * @Rest\Post("/", name="brander_eav_attribute_set_post", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"attributes", "Default"})
     * *Cache(expires="+3 hours")
     * @param Request $request
     * @return AttributeSet
     */
    public function postSetAction(Request $request)
    {
        $attributeSet = $this->deserializeSet($request->getContent());
        if (!$this->securityChecker->isGranted(UniversalManageVoter::CREATE, $attributeSet)) {
            throw new AccessDeniedException();
        }
        $this->em->persist($attributeSet);

        return $this->flush($attributeSet);
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\AttributeSet"
     * )
     *
     * @Rest\Put("/{attributeSet}", name="brander_eav_attribute_set_put", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"attributes", "Default"})
     * *Cache(expires="+3 hours")
     * @param Request      $request
     * @param AttributeSet $attributeSet
     * @return AttributeSet
     */
    public function putSetAction(Request $request, AttributeSet $attributeSet)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::UPDATE, $attributeSet)) {
            throw new AccessDeniedException();
        }
        $attributeSetNew = $this->deserializeSet($request->getContent());

        return $this->flush($attributeSetNew);
    }

    /**
     * @ApiDoc(
     *      output="{'ok': true}"
     * )
     *
     * @Rest\Delete("/{attributeSet}", name="brander_eav_attribute_set_delete", defaults={"_format": "json"})
     * @Rest\View()
     * @param AttributeSet $attributeSet
     * @return array
     */
    public function deleteSetAction(AttributeSet $attributeSet)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::DELETE, $attributeSet)) {
            throw new AccessDeniedException();
        }
        $this->em->flush();

        return ['ok' => true];
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\AttributeSet"
     * )
     *
     * @Rest\Get("/{attributeSet}", name="brander_eav_attribute_set_get", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"attributes", "Default"})
     * @param AttributeSet $attributeSet
     * @return AttributeSet
     */
    public function getSetAction(AttributeSet $attributeSet)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::VIEW, $attributeSet)) {
            throw new AccessDeniedException();
        }

        return $attributeSet;
    }

    /**
     * @param string $content
     * @return AttributeSet
     */
    protected function deserializeSet($content)
    {
        $context = DeserializationContext::create();
        $context->setGroups(["Default", "attributes", "admin"]);

        return $this->serializer->deserialize($content, AttributeSet::class, 'json', $context);
    }
}
