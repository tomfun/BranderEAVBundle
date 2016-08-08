<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Brander\Bundle\EAVBundle\Entity\AttributeGroup;
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
 * @author tomfun
 * @Rest\Route("/eav/rest-group")
 */
class RestGroupController
{
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

    use ValidationTrait;

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\AttributeGroup"
     * )
     *
     * @Rest\Post("/", name="brander_eav_attribute_group_post", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"attributes", "Default", "admin"})
     * *Cache(expires="+3 hours")
     * @param Request $request
     * @return AttributeGroup
     */
    public function postGroupAction(Request $request)
    {
        $attributeGroup = $this->deserializeGroup($request->getContent());
        if (!$this->securityChecker->isGranted(UniversalManageVoter::CREATE, $attributeGroup)) {
            throw new AccessDeniedException();
        }
        $this->em->persist($attributeGroup);

        return $this->flush($attributeGroup);
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\AttributeGroup"
     * )
     *
     * @Rest\Get("/{attributeGroup}", name="brander_eav_attribute_group_get", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"attributes", "Default"})
     * *Cache(expires="+3 hours")
     * @param AttributeGroup $attributeGroup
     * @return AttributeGroup
     */
    public function getGroupAction(AttributeGroup $attributeGroup)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::VIEW, $attributeGroup)) {
            throw new AccessDeniedException();
        }

        return $attributeGroup;
    }

    /**
     * @ApiDoc(
     *      input="Brander\Bundle\EAVBundle\Entity\AttributeGroup",
     *      output="Brander\Bundle\EAVBundle\Entity\AttributeGroup"
     * )
     *
     * @Rest\Put("/{attributeGroup}", name="brander_eav_attribute_group_put", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"attributes", "Default", "admin"})
     * @param Request $request
     * @return AttributeGroup
     */
    public function putGroupAction(Request $request)
    {
        $attributeGroupNew = $this->deserializeGroup($request->getContent());
        if (!$this->securityChecker->isGranted(UniversalManageVoter::UPDATE, $attributeGroupNew)) {
            throw new AccessDeniedException();
        }

        return $this->flush($attributeGroupNew);
    }

    /**
     * @ApiDoc(
     *      input="Brander\Bundle\EAVBundle\Entity\AttributeGroup",
     *      output="{'ok': true}"
     * )
     *
     * @Rest\Delete("/{attributeGroup}", name="brander_eav_attribute_group_delete", defaults={"_format": "json"})
     * @Rest\View()
     * @param AttributeGroup $attributeGroup
     * @return array
     */
    public function deleteGroupAction(AttributeGroup $attributeGroup)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::DELETE, $attributeGroup)) {
            throw new AccessDeniedException();
        }
        $this->em->remove($attributeGroup);
        $this->em->flush();

        return ['ok' => true];
    }

    /**
     * @param string $content
     * @return AttributeGroup
     */
    protected function deserializeGroup($content)
    {
        $context = DeserializationContext::create();
        $context->setGroups(["Default", "attributes", "admin"]);

        return $this->serializer->deserialize($content, AttributeGroup::class, 'json', $context);
    }
}
