<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\CartBundle\Entity\Product;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        if (1===0) {
            $options = array(
                    array(
                        'type' => 'subscription',
                        'title' => 'subscription product', 
                        'description' => 'This is a great subscription',
                        'active' => TRUE, 
                        'days' => 30, 
                        'recurring' => FAlSE, 
                        'price' => 20,
                        'keyImage' => 'http://placekitten.com/200/300',
                        'images' => array('http://placekitten.com/200/300','http://placekitten.com/200/300')
                        ),
                    array(
                        'type' => 'digital',
                        'title' => 'digital product', 
                        'description' => 'This is a great subscription',
                        'active' => TRUE, 
                        'uri' => 'http://private.melissaesplin.com/1-tools-and-supplies.mp4', 
                        'recurring' => FAlSE, 
                        'price' => 50,
                        'keyImage' => 'http://placekitten.com/200/300',
                        'images' => array('http://placekitten.com/200/300','http://placekitten.com/200/300')
                        ),
                    array(
                        'type' => 'gift',
                        'title' => 'gift product percentage', 
                        'description' => 'This is a great subscription',
                        'active' => TRUE, 
                        'price' => 70,
                        'keyImage' => 'http://placekitten.com/200/300',
                        'images' => array('http://placekitten.com/200/300','http://placekitten.com/200/300'),
                        'discountPercent' => .2,
                        'expiration' => 3000,
                        ),
                    array(
                        'type' => 'gift',
                        'title' => 'gift product value', 
                        'description' => 'This is a great subscription',
                        'active' => TRUE, 
                        'price' => 70,
                        'keyImage' => 'http://placekitten.com/200/300',
                        'images' => array('http://placekitten.com/200/300','http://placekitten.com/200/300'),
                        'discountValue' => 23,
                        'expiration' => 3000,
                        ),
                    array(
                        'type' => 'physical',
                        'title' => 'physical product', 
                        'description' => 'This is a great subscription',
                        'active' => TRUE, 
                        'price' => 100,
                        'keyImage' => 'http://placekitten.com/200/300',
                        'images' => array('http://placekitten.com/200/300','http://placekitten.com/200/300'),
                        ),
                );
            
            foreach ($options as $key => $optionArray) {
              $product = new Product();
              foreach ($optionArray as $name => $value) {
                  $product->{"set$name"}($value);
              }
              $manager->persist($product);
              $this->addReference('product_'.$key, $product);
            }
            $manager->flush();
        } else {
            echo "LoadProductData Deactivated\n";
        }
    }
    
    public function getOrder()
    {
        return 25;
    }
}