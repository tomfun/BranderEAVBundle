<?php
namespace Brander\Bundle\EAVBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Brander\Bundle\EAVBundle\Service\PopulateService;

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
        $this->reindexEvent->reindex();

        return (new Response())->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
