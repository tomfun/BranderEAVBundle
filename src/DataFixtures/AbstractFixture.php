<?php
namespace Brander\Bundle\EAVBundle\DataFixtures;

use Brander\Bundle\EAVBundle\Entity as EAV;
use Brander\Bundle\EAVBundle\Model\ExtensibleEntityInterface;
use Brander\Bundle\EAVBundle\Model\GeoLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
abstract class AbstractFixture extends BaseAbstractFixture implements
    FixtureInterface,
    OrderedFixtureInterface,
    ContainerAwareInterface
{
    private static $output = null;
    private static $faker = null;

    /**
     * @param int $verbosity
     * @return null|ConsoleOutput
     */
    public static function getOutput($verbosity = ConsoleOutput::VERBOSITY_VERY_VERBOSE)
    {
        if (!self::$output) {
            self::$output = new ConsoleOutput($verbosity);
        }
        return self::$output;
    }

    /**
     * @param int $max
     * @return null|ProgressBar
     */
    public static function getProgressBar($max = 0)
    {
        $output = self::getOutput();
        if ($output) {
            return new ProgressBar($output, $max);
        }
        return null;
    }

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if ($this->isEnabled()) {
            $this->initialize($this->container);
            $this->loadFixture($manager);
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getContainer()->getParameter('locale');
    }

    /**
     * @return null|\Faker\Generator
     */
    public function getFaker()
    {
        if (!self::$faker) {
            $localeFull = $this->getLocale();
            $localeFull .= '_' . strtoupper($localeFull);
            self::$faker = \Faker\Factory::create($localeFull);
        }
        return self::$faker;
    }

    /**
     * @param EAV\Attribute $attribute
     * @return EAV\Value
     */
    public function createValue(EAV\Attribute $attribute)
    {
        if ($attribute instanceof EAV\AttributeNumeric) {
            $value = new EAV\ValueNumeric();
            $value->setValue($this->getFaker()->numberBetween(0, 200));

        } elseif ($attribute instanceof EAV\AttributeInput) {
            $value = new EAV\ValueInput();
            $value->setValue($this->getFaker()->name);

        } elseif ($attribute instanceof EAV\AttributeTextarea) {
            $value = new EAV\ValueTextarea();
            $value->setValue($this->getFaker()->realText(mt_rand(20, 200)));

        } elseif ($attribute instanceof EAV\AttributeSelect) {
            $value = new EAV\ValueSelect();
            $index = mt_rand(0, $attribute->getOptions()->count() - 1);
            $value->setValue($attribute->getOptions()[$index]);
        } elseif ($attribute instanceof EAV\AttributeBoolean) {
            $value = new EAV\ValueBoolean();
            $value->setValue($this->getFaker()->boolean());
        } elseif ($attribute instanceof EAV\AttributeDate) {
            $value = new EAV\ValueDate();
            $value->setValue($this->getFaker()->date());
        } elseif ($attribute instanceof EAV\AttributeLocation) {
            $value = new EAV\ValueLocation();
            $location = new GeoLocation();
            $location->setLat($this->getFaker()->latitude);
            $location->setLon($this->getFaker()->longitude);
            $value->setValue($location);
        } else {
            throw new \InvalidArgumentException('Wrong attribute class ' . get_class($attribute));
        }

        $value->setAttribute($attribute);


        return $value;
    }

    /**
     * @param ExtensibleEntityInterface $entity
     */
    public function setEavValues(ExtensibleEntityInterface $entity)
    {
        $values = $entity->getValues();
        if (!($values instanceof Collection)) {
            $values = new ArrayCollection(is_array($values) ? $values : []);
            $entity->setValues($values);
        }
        $set = $entity->getAttributeSet();
        foreach ($set->getAttributes() as $attribute) {
            $values->add($this->createValue($attribute));
        }
    }

    /**
     * @param ContainerInterface $container
     */
    public function initialize(ContainerInterface $container)
    {

    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @see \Doctrine\Common\DataFixtures\AbstractFixture::load
     * @param ObjectManager $manager
     */
    abstract public function loadFixture(ObjectManager $manager);

}