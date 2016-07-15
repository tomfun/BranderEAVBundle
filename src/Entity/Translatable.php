<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model\Translatable as Trans;

/**
 * Трейт для переводов
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
trait Translatable
{
    /**
     * @Serializer\Exclude()
     */
    protected $newTranslations;
    /**
     * @Serializer\Exclude()
     */
    protected $currentLocale;

    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationEntityClass()
    {
        return __CLASS__.'Translation';
    }

    /**
     * Returns collection of translations.
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations = $this->translations ?: new ArrayCollection();
    }

    /**
     * Returns collection of new translations.
     *
     * @return ArrayCollection
     */
    public function getNewTranslations()
    {
        return $this->newTranslations = $this->newTranslations ?: new ArrayCollection();
    }

    /**
     * Adds new translation.
     *
     * @param Translation $translation The translation
     *
     * @return $this
     */
    public function addTranslation($translation)
    {
        $this->getTranslations()->set((string) $translation->getLocale(), $translation);
        $translation->setTranslatable($this);

        return $this;
    }

    /**
     * Removes specific translation.
     *
     * @param Translation $translation The translation
     */
    public function removeTranslation($translation)
    {
        $this->getTranslations()->removeElement($translation);
    }

    /**
     * Returns translation for specific locale (creates new one if doesn't exists).
     * If requested translation doesn't exist, it will first try to fallback default locale
     * If any translation doesn't exist, it will be added to newTranslations collection.
     * In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
     * @param bool   $fallbackToDefault Whether fallback to default locale
     *
     * @return Translation
     */
    public function translate($locale = null, $fallbackToDefault = true)
    {
        return $this->doTranslate($locale, $fallbackToDefault);
    }

    /**
     * Merges newly created translations into persisted translations.
     */
    public function mergeNewTranslations()
    {
        foreach ($this->getNewTranslations() as $newTranslation) {
            if (!$this->getTranslations()->contains($newTranslation)) {
                $this->addTranslation($newTranslation);
                $this->getNewTranslations()->removeElement($newTranslation);
            }
        }
    }

    /**
     * @return Returns the current locale
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale ?: $this->getDefaultLocale();
    }

    /**
     * @param mixed $locale the current locale
     */
    public function setCurrentLocale($locale)
    {
        $this->currentLocale = $locale;
    }

    /**
     * @param mixed $locale the default locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * @return Returns the default locale
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @deprecated
     * @Serializer\PostDeserialize
     */
    public function ____update_translations()
    {
        $ret = [];
        /** @noinspection PhpUndefinedFieldInspection */

        if ($this->translations) {
            foreach ($this->translations as $trans) {

                /** @var Trans\Translation $trans */
                if ($trans->isEmpty()) {
                    continue;
                }
                $trans->setTranslatable($this);
                $ret[$trans->getLocale()] = $trans;

            }
        }

        $this->translations = new ArrayCollection($ret);
    }

    /**
     * @param mixed $translations
     */
    public function setATranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return mixed
     */
    public function getATranslations()
    {
        return $this->translations ? array_values($this->translations->toArray()) : $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        $obj = $this->translate($this->getCurrentLocale());
        if (method_exists($obj, 'get'.ucfirst($method))) {
            $method = 'get'.ucfirst($method);
        }

        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Returns translation for specific locale (creates new one if doesn't exists).
     * If requested translation doesn't exist, it will first try to fallback default locale
     * If any translation doesn't exist, it will be added to newTranslations collection.
     * In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
     * @param bool   $fallbackToDefault Whether fallback to default locale
     *
     * @return Translation
     */
    protected function doTranslate($locale = null, $fallbackToDefault = true)
    {
        if (null === $locale) {
            $locale = $this->getCurrentLocale();
        }

        $translation = $this->findTranslationByLocale($locale);
        if ($translation and !$translation->isEmpty()) {
            return $translation;
        }

        if ($fallbackToDefault) {
            if (($fallbackLocale = $this->computeFallbackLocale($locale))
                && ($translation = $this->findTranslationByLocale($fallbackLocale))
            ) {
                return $translation;
            }

            if ($defaultTranslation = $this->findTranslationByLocale($this->getDefaultLocale(), false)) {
                return $defaultTranslation;
            }
        }

        $class = self::getTranslationEntityClass();
        $translation = new $class();
        $translation->setLocale($locale);

        $this->getNewTranslations()->set((string) $translation->getLocale(), $translation);
        $translation->setTranslatable($this);

        return $translation;
    }

    /**
     * An extra feature allows you to proxy translated fields of a translatable entity.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed The translated value of the field for current locale
     */
    protected function proxyCurrentLocaleTranslation($method, array $arguments = [])
    {
        return call_user_func_array(
            [$this->translate($this->getCurrentLocale()), $method],
            $arguments
        );
    }

    /**
     * Finds specific translation in collection by its locale.
     *
     * @param string $locale The locale (en, ru, fr)
     * @param bool   $withNewTranslations searched in new translations too
     *
     * @return Translation|null
     */
    protected function findTranslationByLocale($locale, $withNewTranslations = true)
    {
        $translation = $this->getTranslations()->get($locale);

        if ($translation) {
            return $translation;
        }

        if ($withNewTranslations) {
            return $this->getNewTranslations()->get($locale);
        }
    }

    protected function computeFallbackLocale($locale)
    {
        if (strrchr($locale, '_') !== false) {
            return substr($locale, 0, -strlen(strrchr($locale, '_')));
        }

        return false;
    }
}