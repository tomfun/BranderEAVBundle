<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Service\Security\FakeCollection;
use Brander\Bundle\EAVBundle\Service\Security\UniversalManageVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Return views and js for managing attributes
 *
 * @author tomfun
 * @Rest\Route("/eav/manage")
 */
class ManageController
{
    /**
     * @var AuthorizationCheckerInterface
     *
     * @DI\Inject("security.authorization_checker")
     */
    private $securityChecker;

    /**
     * @Rest\Get("/", name="brander_eav_manage_index")
     * @Rest\View()
     * @Cache(expires="+3 hours", public=true)
     * @return array
     * @throws AccessDeniedException
     */
    public function indexAction()
    {
        if (!$this->securityChecker->isGranted(UniversalManageVoter::MANAGE, new FakeCollection(Attribute::class))) {
            throw new AccessDeniedException();
        }

        return [];
    }
}
