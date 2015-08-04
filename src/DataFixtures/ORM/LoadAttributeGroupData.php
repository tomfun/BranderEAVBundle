<?php
namespace Brander\Bundle\EAVBundle\DataFixtures\ORM;

use Brander\Bundle\EAVBundle\DataFixtures\AbstractFixture;
use Brander\Bundle\EAVBundle\Entity\AttributeGroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

/**
 * Тестовые атрибуты
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class LoadAttributeGroupData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function loadFixture(ObjectManager $manager)
    {
        foreach ($this->getData() as $row) {
            $group = new AttributeGroup();
            $group
                ->setClass($row['class'])
                ->translate($this->getLocale())
                ->setTitle($row['title']);
            $group->mergeNewTranslations();
            $manager->persist($group);

            $this->setReference('brander-eav-attribute-group-' . $group->getClass(), $group);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    private function getData()
    {
        return Yaml::parse(file_get_contents(
                               $this->getContainer()->getParameter(
                                   'brander_eav.fixtures_directory'
                               ) . '/attribute_groups.yml'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
