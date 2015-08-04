<?php
namespace Brander\Bundle\EAVBundle\DataFixtures;

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
            self::$faker = \Faker\Factory::create();
        }
        return self::$faker;
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