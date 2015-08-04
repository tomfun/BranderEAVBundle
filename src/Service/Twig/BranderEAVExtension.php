<?php
namespace Brander\Bundle\EAVBundle\Service\Twig;


use Brander\Bundle\EAVBundle\Entity\Value;
use Brander\Bundle\EAVBundle\Entity\ValueDate;
use Brander\Bundle\EAVBundle\Entity\ValueNumeric;
use Brander\Bundle\EAVBundle\Entity\ValueSelect;
use Doctrine\Common\Persistence\ObjectManager;
use Werkint\Bundle\FrameworkExtraBundle\Twig\AbstractExtension;

/**
 * TWIG-расширение
 *
 * @author Kate Shcherbak <katescherbak@gmail.com>
 */
class BranderEAVExtension extends AbstractExtension
{

    const EXT_NAME = 'brander_eav';

    /**
     * @var array
     */
    private $locales;
    /**
     * @var string
     */
    private $locale;
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param $manager
     * @param array $locales
     * @param string $locale
     * @throws \Exception
     */
    public function __construct($manager, array $locales, $locale)
    {
        $this->manager = $manager;
        $this->locales = $locales;
        $this->locale = $locale;
        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $type = function ($entity) {
            $metadata = $this->manager->getClassMetadata(is_object($entity) ? get_class($entity) : $entity);
            return $metadata;
        };

        $this->globals = [
            'brander_eav_global' => [
                'localeDefault'    => $this->locale,
                'localesSupported' => $this->locales,
            ],
        ];
        $postfix = function ($postfix) {
            $postfix = preg_replace('/(\^(\w+))/', '<sup>$2</sup>', $postfix);
            $postfix = preg_replace('/(_(\w+))/', '<sub>$2</sub>', $postfix);
            return $postfix;
        };
        $this->addFilter('format_postfix', true, $postfix);

        $this->addFilter('brander_eav_type', false, $type);
        $this->addFunction('brander_eav_type', false, $type);

        /**
         * @example {{ value|brander_eav_value({'date': "d.m.Y", 'number': [2, ',', ' ']}) }}
         * @example {{ value|brander_eav_value({'date': "d.m.Y", 'number': [2, ',', ' '], 'postfix': true}) }}
         * @example {{ value|brander_eav_value({'date': "d.m.Y", 'number': [2, ',', ' '], 'postfix': '<h1>%s</h1>'}) }}
         */
        $this->filters['brander_eav_value'] = new \Twig_SimpleFilter(
            'brander_eav_value',
            function (\Twig_Environment $env, Value $value, array $format = []) use (&$postfix) {
                $out = '';
                $formatDefault = $env->getGlobals();
                if (isset($formatDefault['brander_eav_value_format']) && $formatDefault = $formatDefault['brander_eav_value_format']) {
                    $format = array_merge($formatDefault, $format);
                }
                if ($value instanceof ValueDate) {
                    $date = $value->getValue();
                    if (!$date) {
                        $out = '-';
                    } else {
                        $filter = $env->getFilter('date');
                        $formatDate = isset($format['date']) ? $format['date'] : null;
                        $call = $filter->getCallable();
                        if ($filter->needsEnvironment()) {
                            $out = $call($env, $date, $formatDate);
                        } else {
                            $out = $call($date, $formatDate);
                        }
                    }
                } elseif ($value instanceof ValueNumeric) {
                    $number = $value->getValue();
                    if ($number) {
                        $filter = $env->getFilter('number_format');
                        $formatNumber = isset($format['number']) ? $format['number'] : [];
                        $formatNumber = array_merge($formatNumber, [null, null, null]);
                        $call = $filter->getCallable();
                        if ($filter->needsEnvironment()) {
                            $out = $call($env, $number, $formatNumber[0], $formatNumber[1], $formatNumber[2]);
                        } else {
                            $out = $call($number, $formatNumber[0], $formatNumber[1], $formatNumber[2]);
                        }
                    }
                } elseif ($value instanceof ValueSelect) {
                    $out = $value->getOption()->getTitle();
                } else {
                    $out = $value->getValue() ? $value->getValue() : '';
                }
                if (isset($format['postfix']) && $format['postfix'] && ($pf = $value->getAttribute()->getPostfix())) {
                    $pf = $postfix($pf);
                    $out = $out . ($format['postfix'] === true ? ' ' . $pf : sprintf($format['postfix'], $pf));
                }
                return $out;
            },
            ['needs_environment' => true, 'is_safe' => ['all']]
        );
    }
}
