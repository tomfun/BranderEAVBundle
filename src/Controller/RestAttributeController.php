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
        $this->em->persist($attribute);
        if ($attribute instanceof AttributeSelect) {
            foreach ($attribute->getOptions() as $option) {
                $this->em->persist($option->setAttribute($attribute));
            }
        }

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
            $this->em->remove($attribute);
            $this->em->persist($attributeNew);
            foreach ($attribute->getTranslations() as $trans) {
                $this->em->detach($trans);
                $trans->setTranslatable($attributeNew);
                $trans->setId(null);
                $this->em->persist($trans);
            }
            $attributeNew->mergeNewTranslations();
        } else {
            $attributeNew->setId($attribute->getId());

            $ret = $attribute->getTranslations()->toArray();
            if ($attributeNew->getTranslations()) {
                /** @var AttributeTranslation $trans */
                foreach ($attributeNew->getTranslations() as $trans) {
                    if ($trans->isEmpty()) {
                        continue;
                    }
                    $trans->setTranslatable($attribute);
                    if (isset($ret[$trans->getLocale()])) {
                        $trans->setId($ret[$trans->getLocale()]->getId());
                    }
                    $ret[$trans->getLocale()] = $this->em->merge($trans);
                }
                foreach ($attributeNew->getNewTranslations() as $trans) {
                    if ($trans->isEmpty()) {
                        continue;
                    }
                    $trans->setTranslatable($attribute);
                    if (isset($ret[$trans->getLocale()])) {
                        $trans->setId($ret[$trans->getLocale()]->getId());
                    }
                    $ret[$trans->getLocale()] = $this->em->merge($trans);
                }
            }
            $attributeNew->getTranslations()->clear();

            $attribute = $this->em->merge($attributeNew);
            $attribute->setATranslations(new ArrayCollection($ret));
            $attributeNew->setATranslations($attribute->getTranslations());

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
     * @return array
     * @throws AccessDeniedException
     */
    public function deleteAction(Attribute $attribute)
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::DELETE, $attribute)) {
            throw new AccessDeniedException();
        }
        $this->em->remove($attribute);
        $this->em->flush();

        return ['ok' => true];
    }

    /**
     * @ApiDoc(
     *      output="Brander\Bundle\EAVBundle\Entity\Attribute"
     * )
     *
     * @Rest\Get("/{attribute}", name="brander_eav_attribute_get", defaults={"_format": "json"})
     * @Rest\View(serializerGroups={"Default"})
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

        $used = $this->repoValue->getUsed($attribute);

        if ($attribute instanceof AttributeSelect) {
            foreach ($attribute->getOptions() as $option) {
                $oldOptions[$option->getId()] = $option;
            }
            foreach ($used as $value) {
                $usedOptions[$value->getOption()->getId()] = $value->getOption();
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
        $context->setGroups(["admin", "Default"]);

        $contentJson = json_decode($content, true);
        if (isset($contentJson['id'])) {
            unset($contentJson['id']);
            $content = json_encode($contentJson);
        }

        /** @var Attribute $attributeNew */

        return $this->serializer->deserialize($content, Attribute::class, 'json', $context);
    }
}
