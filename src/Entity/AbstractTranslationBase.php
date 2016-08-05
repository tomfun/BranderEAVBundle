<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractTranslationBase
 * @package Brander\Bundle\EAVBundle\Entity
 */
abstract class AbstractTranslationBase
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * *ORM\Column(name="id", type="string", length=Werkint\Bundle\FrameworkExtraBundle\Service\Util\IdGenerator::MAX_LENGTH, nullable=false)
     * *ORM\GeneratedValue(strategy="CUSTOM")
     * *ORM\CustomIdGenerator(class="Werkint\Bundle\FrameworkExtraBundle\Service\Util\IdGenerator")
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=6)
     */
    protected $locale;
    /**
     * Will be mapped to translatable entity
     * by TranslatableSubscriber
     */
    protected $translatable;

    /**
     * Returns the translatable entity class name.
     *
     * @return string
     */
    public static function getTranslatableEntityClass()
    {
        // By default, the translatable class has the same name but without the "Translation" suffix
        return substr(__CLASS__, 0, -11);
    }

    /**
     * Returns object id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return Translatable
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param Translatable $translatable The translatable
     *
     * @return $this
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * Returns this translation locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets locale name for this translation.
     *
     * @param string $locale The locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Tells if translation is empty
     *
     * @return bool true if translation is not filled
     */
    public function isEmpty()
    {
        return false;
    }
}
