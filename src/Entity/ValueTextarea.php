<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Значение аттрибута для textarea
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueTextarea extends Value
{
    const ELASTICA_POSTFIX_EN = '_eavfltxt_en';
    const ELASTICA_POSTFIX_ES = '_eavfltxt_es';
    const ELASTICA_POSTFIX_FR = '_eavfltxt_fr';
    const ELASTICA_POSTFIX_RU = '_eavfltxt_ru';
}
