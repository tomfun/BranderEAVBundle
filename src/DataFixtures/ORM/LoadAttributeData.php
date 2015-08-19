<?php
namespace Brander\Bundle\EAVBundle\DataFixtures\ORM;

use Brander\Bundle\EAVBundle\DataFixtures\AbstractFixture;
use Brander\Bundle\EAVBundle\Entity\Attribute;
use Brander\Bundle\EAVBundle\Entity\AttributeGroup;
use Brander\Bundle\EAVBundle\Entity\AttributeSelect;
use Brander\Bundle\EAVBundle\Entity\AttributeSelectOption;
use Brander\Bundle\EAVBundle\Service\Holder;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Тестовые атрибуты
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class LoadAttributeData extends AbstractFixture
{
    protected static $attributeCount = 0;
    /** @var  Holder */
    protected $attribute;

    /**
     * @return int
     */
    public static function getAttributeCount() {
        return static::$attributeCount;
    }

    /**
     * @param AbstractFixture $fixture
     * @return Attribute[]
     */
    public static function getArray(AbstractFixture $fixture) {
        $res = [];
        foreach(range(0, static::$attributeCount - 1) as $i) {
            $res[] = $fixture->getReference('brander-eav-attribute-' . $i);
        }
        return $res;
    }

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * {@inheritdoc}
     */
    public function initialize(ContainerInterface $container)
    {
        $this->faker = $this->getFaker();
        $this->attribute = $container->get('brander_eav.attribute.holder');
    }


    /**
     * {@inheritdoc}
     */
    public function loadFixture(ObjectManager $manager)
    {
        $i = static::$attributeCount;
        foreach ($this->getData() as $row) {
            /** @var Attribute $attribute */
            $attribute = $this->createAttribute($row, $manager);

            if (isset($row['group']) && ($groupRef = $row['group'])) {
                if (!is_array($groupRef)) {
                    $groupRef = [$groupRef];
                }
                foreach ($groupRef as $ref) {
                    /** @var AttributeGroup $group */
                    $group = $this->getReference('brander-eav-attribute-group-' . $ref);
                    $group->getAttributes()->add($attribute);
                }
            }
            $manager->persist($attribute);
            $this->setReference('brander-eav-attribute-' . $i++, $attribute);
        }
        static::$attributeCount = $i;
        $manager->flush();
    }


    /**
     * @param array         $attribute
     * @param ObjectManager $manager
     * @return Attribute
     */
    private function createAttribute(array $attribute, ObjectManager $manager)
    {
        $attr = $this->attribute->createFromShortName($attribute['type']);
        $attr->translate($this->getLocale());
        if ($attr instanceof AttributeSelect) {
            foreach (range(0, mt_rand(2, 5)) as $i) {
                $option = new AttributeSelectOption();
                $option->translate('ru')->setTitle($this->faker->name);
                $option->mergeNewTranslations();
                $attr->getOptions()->add($option);
                $option->setAttribute($attr);
                $manager->persist($option);
            }
        }
        if (isset($attribute['filterType'])) {
            $attr->setFilterType($attribute['filterType']);
        }
        if (isset($attribute['filterOrder'])) {
            $attr->setFilterOrder((int)$attribute['filterOrder']);
        }

        $localized = $attr->translate($this->getLocale());
        $localized->setTitle($attribute['title']);

        if (isset($attribute['hint'])) {
            $localized->setHint($attribute['hint']);
        }

        if (isset($attribute['placeholder'])) {
            $localized->setPlaceholder($attribute['placeholder']);
        }

        if (isset($attribute['postfix'])) {
            $localized->setPostfix($attribute['postfix']);
        }
        $attr->mergeNewTranslations();

        $attr->setIsRequired(
            isset($attribute['required']) ? $attribute['required'] : $this->faker->boolean(40)
        );
        $attr->setIsFilterable(
            isset($attribute['filterable']) ? $attribute['filterable'] : $this->faker->boolean(50)
        );
        $attr->setIsSortable(
            isset($attribute['sortable']) ? $attribute['sortable'] : $this->faker->boolean(20)
        );

        return $attr;
    }

    /**
     * @return array
     */
    private function getData()
    {
        return Yaml::parse(file_get_contents(
                               $this->getContainer()->getParameter('brander_eav.fixtures_directory') . '/attributes.yml'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
