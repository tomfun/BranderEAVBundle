<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Werkint\Bundle\FrameworkExtraBundle\Model\Translation;

/**
 * @method string getId
 */
abstract class AbstractTranslationBase
{
    use Translation;
}