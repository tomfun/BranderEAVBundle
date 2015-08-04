<?php
namespace Brander\Bundle\EAVBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
trait ValidationTrait
{

    /**
     * @var EntityManagerInterface
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    private $em;

    /**
     * @var ValidatorInterface
     * @DI\Inject("validator")
     */
    private $validator;

    /**
     * @param $object
     * @return Response|void
     */
    protected function flush($object)
    {
        $validationErrors = $this->validator->validate($object);
        foreach ($validationErrors as $error) {
            $errorsList[] = $error->getMessage();
        }
        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $errorsList[] = $e->getMessage();
            return new Response(json_encode($errorsList), 418);
        }
        return $object;
    }
}