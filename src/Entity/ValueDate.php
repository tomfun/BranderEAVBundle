<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Значение аттрибута для дат
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueDate extends Value
{
    /**
     * @Serializer\Accessor(getter="getValue",setter="setValue")
     * @Serializer\Type("DateTime")
     * @Serializer\Expose()
     * @var string|null
     */
    protected $value;

    /**
     * @var \DateTime
     */
    protected $valueTyped = null;

    /**
     * @param \DateTime|string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->valueTyped = $value instanceof \DateTime ? $value : new \DateTime($value);
        $this->value = $this->valueTyped->getTimestamp();
        return $this;
    }


    /**
     * @return \DateTime|null
     */
    public function getValue()
    {
        if (!$this->valueTyped) {
            if ($this->value) {
                $this->valueTyped = new \DateTime();
                $this->valueTyped->setTimestamp($this->value);
            }
        }
        return $this->valueTyped;
    }
}
