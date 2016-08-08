<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Для переводов. Нельзя создать базовый класс, иначе будут проблемы с сериалайзером.
 * @property $translations Collection
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
trait Translatable
{
    /**
     * Returns collection of translations.
     *
     * @return Collection
     */
    public function getTranslations()
    {
        if (!$this->translations) {
            $this->translations = [];
        }
        if (!($this->translations instanceof Collection)) {
            $this->translations = new ArrayCollection($this->translations);
        }
        return $this->translations;
    }

    /**
     * Returns array of translations.
     *
     * @return array
     */
    public function getTranslationsByLocale()
    {
        if (!($arColl = $this->getTranslations())) {

            return [];
        }
        $out = [];
        foreach ($arColl as $trans) {
            $out[$trans->getLocale()] = $trans;
        }

        return $out;
    }
    /**
     * Returns array of translations with id as key.
     *
     * @return array
     */
    public function getTranslationsById()
    {
        if (!($arColl = $this->getTranslations())) {

            return [];
        }
        $out = [];
        foreach ($arColl as $trans) {
            if (!$trans->getId()) {
                continue;
            }
            $out[$trans->getId()] = $trans;
        }

        return$out;
    }
}