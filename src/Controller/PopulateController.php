<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Brander\Bundle\EAVBundle\Service\Security\FakeCollection;
use Brander\Bundle\EAVBundle\Service\Security\UniversalManageVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Brander\Bundle\EAVBundle\Service\PopulateService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Brander\Bundle\EAVBundle\Entity\Attribute;

/**
 * @author mom <alinyonish@gmail.com>
 * @Rest\Route("/eav")
 */
class PopulateController
{
    /**
     * @DI\Inject("brander_eav.reindex_elastica")
     * @var PopulateService
     */
    private $reindexEvent;

    /**
     * @var AuthorizationCheckerInterface
     *
     * @DI\Inject("security.authorization_checker")
     */
    private $securityChecker;

    /**
     * Elastica populate indexes
     * @ApiDoc(
     *     statusCodes={
     *         204="Returned when successful"
     *     }
     * )
     * @Rest\Patch("/reindex", name="brander_eav_reindex",
     *      defaults={"_format": "json"}
     * )
     * @return Response
     */
    public function reindexAction()
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::MANAGE, new FakeCollection(Attribute::class))) {
            throw new AccessDeniedException();
        }
        $this->reindexEvent->reindex();

        return (new Response())->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
