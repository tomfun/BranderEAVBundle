<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tomfun <tomfun1990@gmail.com>
 */
abstract class AbstractTranslation extends AbstractTranslationBase
{
    /**
     * Will be mapped to translatable entity
     * by TranslatableSubscriber
     * @Serializer\Groups("=false")
     * @Serializer\Exclude()
     */
    protected $translatable;
    /**
     * *Serializer\Type("string")
     * *ORM\Column(name="id", type="string", length=Werkint\Bundle\FrameworkExtraBundle\Service\Util\IdGenerator::MAX_LENGTH, nullable=false)
     * *ORM\GeneratedValue(strategy="CUSTOM")
     * *ORM\CustomIdGenerator(class="Werkint\Bundle\FrameworkExtraBundle\Service\Util\IdGenerator")
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="brander.eav.locale")
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @var string
     */
    protected $locale;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="brander.eav.title")
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @var string
     */
    protected $title;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->title);
    }
}