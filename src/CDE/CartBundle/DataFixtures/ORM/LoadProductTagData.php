<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\ContentBundle\Entity\Tag;
use CDE\CartBundle\Entity\Product;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProductTagData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
             $topTagNames = array(
                'Calligraphy',
                'Photoshop',
                'Illustrator',
                'Photography',
             );
             
             
             for ($i=0; $i <=2 ; $i++) { 
                $option = $this->getReference('product_'.$i);
                $topTagNumber = rand(0,3);
                $subTagNumber = rand(0,2);
                $topTag = $this->getReference($topTagNames[$topTagNumber]);
                $subTag = $this->getReference($topTagNames[$topTagNumber].'-'.$topTags[$topTagNames[$topTagNumber]][$subTagNumber]);
                $option->addTag($topTag);
                $option->addTag($subTag);
                $manager->persist($option);
             }
    
            $manager->flush();
        } else {
            echo "LoadProductTagData Deactivated.\n";
        }
    }
    
    public function getOrder()
    {
        return 65;
    }
}