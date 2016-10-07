<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\AttributeSelect;
use Brander\Bundle\EAVBundle\Entity\AttributeTranslation;
use Brander\Bundle\EAVBundle\Repo\Value;
use Brander\Bundle\EAVBundle\Service\Security\UniversalManageVoter;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manage attributes via REST
 * @author tomfun
 * @Rest\Route("/eav/rest-attribute")
 */
class RestAttributeController
{

    use ValidationTrait;

    /**
     * @var Value
     * @DI\Inject("brander_eav.repo.value")
     */
    private $repoValue;

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
     *      output="Brander\Bundle\EAVBundle\Entity\Attribute"
     * )
     *
     * @Rest\Post("/", name="brander_eav_attribute_post", defaults={"_format": "json"})
     * @Rest\View()
     * @param Request $request
     * @return Attribute
     * @throws AccessDeniedException
     */
    public function postAction(Request $request)
    {
        $attribute = $this->deserialize($request->getContent());
        if (!$this->securityChecker->isGranted(UniversalManageVoter::CREATE, $attribute)) {
            throw new AccessDeniedException();
        }
        foreach ($attribute->getTranslations() as $attrTrans) {
            $attrTrans->setTranslatable($attribute);
        }
        if ($attribute instanceof AttributeSelect) {
            foreach ($attribute->getOptions() as $option) {
                $option->setAttribute($attribute);
                foreach ($option->getTranslations() as $optTrans) {
                    $optTrans->setTranslatable($option);
                }
            }
        }
        $this->em->persist($attribute);

        return $this->flush($attribute);
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\Attribute"
     * )
     *
     * @Rest\Put("/{attribute}", name="brander_eav_attribute_put", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"Default", "translations"})
     * @param Request   $request
     * @param Attribute $attribute
     * @return Attribute
     * @throws AccessDeniedException
     */
    public function putAction(Request $request, Attribute $attribute)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::UPDATE, $attribute)) {
            throw new AccessDeniedException();
        }
        $attributeNew = $this->deserialize($request->getContent());
        $checkResult = $this->check($attribute, $attributeNew);
        if ($checkResult['recreate']) {
            /** @var AttributeTranslation[] $allTranslations */
            $allTranslations = $attribute->getTranslations()->toArray();
            $allTranslations +=$attributeNew->getTranslations()->toArray();
            foreach ($allTranslations as $attrTrans) {
                $attrTrans->setTranslatable($attributeNew);
            }
            $this->em->remove($attribute);
            $this->em->persist($attributeNew);
        } else {
            $attributeNew->setId($attribute->getId());

            /** @var AttributeTranslation[] $allTranslations */
            $oldTranslations = $attribute->getTranslationsById();
            /** @var AttributeTranslation[] $ret */
            $ret = $attributeNew->getTranslationsByLocale();
            foreach ($ret as $attrTrans) {
                $attrTrans->setTranslatable($attributeNew);
                if ($attrTrans->getId()) {
                    unset($oldTranslations[$attrTrans->getId()]);
                }
            }

            foreach ($oldTranslations as $forDelete) {
                $this->em->remove($forDelete);
            }

            $attribute = $this->em->merge($attributeNew);

            if ($attributeNew instanceof AttributeSelect) {
                foreach ($attributeNew->getOptions() as $option) {
                    $option->setAttribute($attribute);
                }
            }
        }
        if (count($checkResult['optionsRemove'])) {
            foreach ($checkResult['optionsRemove'] as $option) {
                $this->em->remove($option);
            }
        }
        if (count($checkResult['optionsNew'])) {
            foreach ($checkResult['optionsNew'] as $option) {
                $this->em->persist($option);
            }
        }

        return $this->flush($attributeNew);
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\Attribute"
     * )
     *
     * @Rest\Patch("/{attribute}", name="brander_eav_attribute_check", defaults={"_format": "json"})
     * @Rest\View()
     * @param Request   $request
     * @param Attribute $attribute
     * @return Attribute
     * @throws AccessDeniedException
     */
    public function checkAction(Request $request, Attribute $attribute)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::UPDATE, $attribute)) {
            throw new AccessDeniedException();
        }
        $attributeNew = $this->deserialize($request->getContent());

        return $this->check($attribute, $attributeNew);
    }

    /**
     * @ApiDoc(
     *      output="{'ok': true}"
     * )
     *
     * @Rest\Delete("/{attribute}", name="brander_eav_attribute_delete", defaults={"_format": "json"})
     * @Rest\View()
     * @param Attribute $attribute
     * @return Response
     * @throws AccessDeniedException
     */
    public function deleteAction(Attribute $attribute)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::DELETE, $attribute)) {
            throw new AccessDeniedException();
        }
        $this->em->remove($attribute);
        $this->em->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\Attribute"
     * )
     *
     * @Rest\Get("/{attribute}", name="brander_eav_attribute_get", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"Default", "translations"})
     * @param Attribute $attribute
     * @return Attribute
     * @throws AccessDeniedException
     */
    public function getAction(Attribute $attribute)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::VIEW, $attribute)) {
            throw new AccessDeniedException();
        }

        return $attribute;
    }

    /**
     * @param Attribute $attribute
     * @param Attribute $attributeNew
     * @return array
     */
    protected function check(Attribute $attribute, Attribute $attributeNew)
    {
        $oldOptions = [];
        $usedOptions = [];
        $newOptions = [];

        if ($attribute instanceof AttributeSelect) {
            $used = $this->repoValue->getUsedSelectOptions($attribute);
            foreach ($attribute->getOptions() as $option) {
                $oldOptions[$option->getId()] = $option;
            }
            foreach ($used as $option) {
                $usedOptions[$option->getId()] = $option;
            }
        }
        if (($attributeNew instanceof AttributeSelect)) {
            if ($attribute instanceof AttributeSelect && $attributeNew->getOptions()) {
                foreach ($attributeNew->getOptions() as $option) {
                    if ($option->getId() && isset($oldOptions[$option->getId()])) {
                        unset($oldOptions[$option->getId()]);
                    }
                    if ($option->getId() && isset($usedOptions[$option->getId()])) {
                        unset($usedOptions[$option->getId()]);
                    }
                    if (!$option->getId()) {
                        $newOptions[] = $option;
                    }
                }
            }
        }

        return [
            'optionsNew'        => array_values($newOptions),
            'optionsUsed'       => array_values($usedOptions),
            'optionsRemove'     => array_values($oldOptions),
            'optionsRemoveUsed' => array_values(array_intersect_key($oldOptions, $usedOptions)),
            'recreate'          => get_class($attribute) != get_class($attributeNew),
        ];
    }

    /**
     * @param string $content
     * @return Attribute
     */
    protected function deserialize($content)
    {
        $context = DeserializationContext::create();
        $context->setGroups(["admin", "Default", "attributeselect_with_options"]);

        $contentJson = json_decode($content, true);
        if (isset($contentJson['id'])) {
            unset($contentJson['id']);
            $content = json_encode($contentJson);
        }

        return $this->serializer->deserialize($content, Attribute::class, 'json', $context);
    }
}
