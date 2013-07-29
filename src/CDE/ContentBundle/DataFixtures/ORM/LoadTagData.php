<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\ContentBundle\Entity\Tag;
use CDE\ContentBundle\Entity\Page;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTagData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        if (1===0) {
            $topTags = array(
                    'Calligraphy' => array('Video', 'Printables', 'Downloads'),
                    'Photoshop' => array('Video', 'Printables', 'Downloads'),
                    'Illustrator' => array('Video', 'Printables', 'Downloads'),
                    'Photography' => array('Video', 'Printables', 'Downloads'),
                );
                
            foreach ($topTags as $topTag => $subTags) {
                $tag = new Tag();
                $tag->setName($topTag);
                $tag->setActive(TRUE);
                $this->addReference($topTag, $tag);
                $manager->persist($tag);
                foreach ($subTags as $subTag) {
                    $tag2 = new Tag();
                    $tag2->setName($subTag);
                    $tag2->setParent($tag);
                    $tag2->setActive(TRUE);
                    $this->addReference($topTag.'-'.$subTag, $tag2);
                    $manager->persist($tag2);
                }
            }
    
            $manager->flush();            
        } else {
            echo "LoadTagData Deactivated\n";
        }
    }
    
    public function getOrder()
    {
        return 40;
    }
}