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
class BranderEAVExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    const EXT_NAME = 'brander_eav';

    /** @var ObjectManager */
    protected $manager;
    /** @var \Twig_SimpleFilter[] */
    protected $filters = [];
    /** @var \Twig_SimpleFunction[] */
    protected $functions = [];
    protected $globals = [];
    /**
     * @var array
     */
    private $locales;
    /**
     * @var string
     */
    private $locale;

    /**
     * @param ObjectManager $manager
     * @param array         $locales
     * @param string        $locale
     * @throws \Exception
     */
    public function __construct(ObjectManager $manager, array $locales, $locale)
    {
        $this->manager = $manager;
        $this->locales = $locales;
        $this->locale = $locale;
        $this->init();
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     * @return callable
     */
    public function getFilter($name)
    {
        if (!isset($this->filters[$name])) {
            throw new \InvalidArgumentException('Filter not found: '.$name);
        }

        return $this->filters[$name]->getCallable();
    }

    // -- Stuff ---------------------------------------

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     * @return callable
     */
    public function getFunction($name)
    {
        if (!isset($this->functions[$name])) {
            throw new \InvalidArgumentException('Function not found: '.$name);
        }

        return $this->functions[$name]->getCallable();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::EXT_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * @param string   $name
     * @param bool     $isSafe
     * @param callable $callable
     */
    protected function addFilter(
        $name,
        $isSafe,
        callable $callable
    )
    {
        $safe = ['is_safe' => ['all']];
        $this->filters[$name] = new \Twig_SimpleFilter($name, $callable, $isSafe ? $safe : []);
    }

    /**
     * @param string   $name
     * @param bool     $isSafe
     * @param callable $callable
     */
    protected function addFunction(
        $name,
        $isSafe,
        callable $callable
    )
    {
        $safe = ['is_safe' => ['all']];
        $this->functions[$name] = new \Twig_SimpleFunction($name, $callable, $isSafe ? $safe : []);
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
                    $out = $out.($format['postfix'] === true ? ' '.$pf : sprintf($format['postfix'], $pf));
                }

                return $out;
            },
            ['needs_environment' => true, 'is_safe' => ['all']]
        );
    }
}
