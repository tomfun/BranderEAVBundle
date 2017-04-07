<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\AttributeGroup;
use Brander\Bundle\EAVBundle\Entity\AttributeSet;
use Brander\Bundle\EAVBundle\Service\Filter\FilterHolder;
use Brander\Bundle\EAVBundle\Service\Holder;
use Brander\Bundle\EAVBundle\Service\Security\FakeCollection;
use Brander\Bundle\EAVBundle\Service\Security\UniversalManageVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author tomfun
 * @Rest\Route("/eav/collections")
 */
class RestPublicController
{
    /**
     * @var SerializerInterface
     * @DI\Inject("serializer")
     */
    private $serializer;

    /**
     * @var EntityRepository
     * @DI\Inject("brander_eav.repo.group")
     */
    private $repoGroup;

    /**
     * @var EntityRepository
     * @DI\Inject("brander_eav.repo.set")
     */
    private $repoSet;

    /**
     * @var EntityRepository
     * @DI\Inject("brander_eav.repo.attribute")
     */
    private $repoAttr;

    /**
     * @var Holder
     *
     * @DI\Inject("brander_eav.attribute.holder")
     */
    private $attributeHolder;

    /**
     * @var AuthorizationCheckerInterface
     *
     * @DI\Inject("security.authorization_checker")
     */
    private $securityChecker;
    /**
     * @var FilterHolder
     *
     * @DI\Inject("brander_eav.filter.holder")
     */
    private $holder;

    /**
     * @ApiDoc(
     *      output="array<Brander\Bundle\EAVBundle\Entity\AttributeSet>"
     * )
     *
     * @Rest\Get("/set-list/{manage}", name="brander_eav_set_list", defaults={"_format": "json", "manage":false})
     * @Rest\View()
     * @param bool $manage
     * @return array
     */
    public function collectionSetAction($manage = false)
    {
        $list = $this->repoSet->findAll();
        $cxt = SerializationContext::create();
        if ($manage) {
            if (!$this->securityChecker->isGranted(
                UniversalManageVoter::MANAGE,
                new FakeCollection(AttributeSet::class)
            )
            ) {
                throw new AccessDeniedException();
            }
            $cxt->setGroups(['Default', 'attributes', 'attributeselect_with_options', 'translations']);
        }

        return $this->response($list, $manage, $cxt);
    }

    /**
     * @ApiDoc(
     *      output="array<Brander\Bundle\EAVBundle\Entity\AttributeGroup>"
     * )
     *
     * @Rest\Get("/group-list/{manage}", name="brander_eav_group_list", defaults={"_format": "json"})
     * @Rest\View()
     * @param bool $manage
     * @return array
     */
    public function collectionGroupAction($manage = false)
    {
        $list = $this->repoGroup->findAll();
        $cxt = SerializationContext::create();
        $cxt->setGroups(['Default', 'translations']);

        return $this->response($list, $manage, $cxt);

    }

    /**
     * @ApiDoc(
     *      output="array<Brander\Bundle\EAVBundle\Entity\Attribute>"
     * )
     *
     * @Rest\Get("/attr-list/{manage}", name="brander_eav_attribute_list", defaults={"_format": "json"})
     * @Rest\View()
     * @param bool $manage
     * @return array
     */
    public function collectionAttributeAction($manage = false)
    {
        $list = $this->repoAttr->findAll();
        $cxt = SerializationContext::create();
        if ($manage) {
            if (!$this->securityChecker->isGranted(
                UniversalManageVoter::MANAGE,
                new FakeCollection(Attribute::class)
            )
            ) {
                throw new AccessDeniedException();
            }
            $cxt->setGroups(['Default', 'translations']);
        }

        return $this->response($list, $manage, $cxt);
    }

    /**
     * @ApiDoc(
     *      output="json"
     * )
     *
     * @Rest\Get("/attr-type-list", name="brander_eav_attribute_type_list", defaults={"_format": "json"})
     * @Rest\View()
     * @Cache(expires="+6 hours", public=true)
     * @return array
     */
    public function collectionAttributeTypesAction()
    {
        return $this->attributeHolder->getAttributeMap();
    }

    /**
     * @ApiDoc(
     *      output="array", description="get data about filters"
     * )
     *
     * @Rest\Get("/filter-view-list", name="brander_eav_filter_list", defaults={"_format": "json"})
     * @Cache(expires="+1 day", public=true)
     * @Rest\View()
     * @return array<string, string>
     */
    public function getFiltersAction()
    {
        return $this->holder->getJsModels();
    }

    /**
     * @param array|ArrayCollection     $list
     * @param bool                      $manage
     * @param null|SerializationContext $cxt
     * @return Response
     */
    protected function response($list, $manage, SerializationContext $cxt = null)
    {
        $data = $this->serializer->serialize($list, 'json', $cxt);
        $resp = new Response($data);
        if (!$manage) {
            $resp->setPublic()->setMaxAge(6 * 60 * 60)->setExpires(new \DateTime("+4 hours"));
        }

        return $resp;
    }
}
