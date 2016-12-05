<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Мультиселект
 *
 * @author mom <alinyonish@gmail.com>
 *
 * @ORM\Entity()
 */
class AttributeMultiSelect extends AttributeSelect
{
}
