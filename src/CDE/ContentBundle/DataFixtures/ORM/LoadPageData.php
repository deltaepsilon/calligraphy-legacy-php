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

class LoadPageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
                
             $pages = array(
                    'calligraphy1' => array('html' => '<h1>Calligraphy1 HTML</h1>', 'css' => 'h1{color:blue;}'),
                    'calligraphy2' => array('html' => '<h1>Calligraphy2 HTML</h1>', 'css' => 'h1{color:green;}'),
                    'calligraphy3' => array('html' => '<h1>Calligraphy3 HTML</h1>', 'css' => 'h1{color:purple;}'),
                    'calligraphy4' => array('html' => '<h1>Calligraphy4 HTML</h1>', 'css' => 'h1{color:brown;}'),
                    'calligraphy5' => array('html' => '<h1>Calligraphy5 HTML</h1>', 'css' => 'h1{color:gold;}'),
                    
                    'photoshop1' => array('html' => '<h1>photoshop1 HTML</h1>', 'css' => 'h1{color:blue;}'),
                    'photoshop2' => array('html' => '<h1>photoshop2 HTML</h1>', 'css' => 'h1{color:green;}'),
                    'photoshop3' => array('html' => '<h1>photoshop3 HTML</h1>', 'css' => 'h1{color:purple;}'),
                    'photoshop4' => array('html' => '<h1>photoshop4 HTML</h1>', 'css' => 'h1{color:brown;}'),
                    'photoshop5' => array('html' => '<h1>photoshop5 HTML</h1>', 'css' => 'h1{color:gold;}'),
                    
                    'illustrator1' => array('html' => '<h1>illustrator1 HTML</h1>', 'css' => 'h1{color:blue;}'),
                    'illustrator2' => array('html' => '<h1>illustrator2 HTML</h1>', 'css' => 'h1{color:green;}'),
                    'illustrator3' => array('html' => '<h1>illustrator3 HTML</h1>', 'css' => 'h1{color:purple;}'),
                    'illustrator4' => array('html' => '<h1>illustrator4 HTML</h1>', 'css' => 'h1{color:brown;}'),
                    'illustrator5' => array('html' => '<h1>illustrator5 HTML</h1>', 'css' => 'h1{color:gold;}'),
                    
                    'illustrator1' => array('html' => '<h1>illustrator1 HTML</h1>', 'css' => 'h1{color:blue;}'),
                    'illustrator2' => array('html' => '<h1>illustrator2 HTML</h1>', 'css' => 'h1{color:green;}'),
                    'illustrator3' => array('html' => '<h1>illustrator3 HTML</h1>', 'css' => 'h1{color:purple;}'),
                    'illustrator4' => array('html' => '<h1>illustrator4 HTML</h1>', 'css' => 'h1{color:brown;}'),
                    'illustrator5' => array('html' => '<h1>illustrator5 HTML</h1>', 'css' => 'h1{color:gold;}'),
                    
                    'photography1' => array('html' => '<h1>photography1 HTML</h1>', 'css' => 'h1{color:blue;}'),
                    'photography2' => array('html' => '<h1>photography2 HTML</h1>', 'css' => 'h1{color:green;}'),
                    'photography3' => array('html' => '<h1>photography3 HTML</h1>', 'css' => 'h1{color:purple;}'),
                    'photography4' => array('html' => '<h1>photography4 HTML</h1>', 'css' => 'h1{color:brown;}'),
                    'photography5' => array('html' => '<h1>photography5 HTML</h1>', 'css' => 'h1{color:gold;}'),
                 );
            foreach ($pages as $key => $pageArray) {
                $page = new Page();
                $page->setTitle($key);
                $page->setHtml($pageArray['html']);
                $page->setCss($pageArray['css']);
                $topTagNumber = rand(0,3);
                $subTagNumber = rand(0,2);
                $topTag = $this->getReference($topTagNames[$topTagNumber]);
                $subTag = $this->getReference($topTagNames[$topTagNumber].'-'.$topTags[$topTagNames[$topTagNumber]][$subTagNumber]);
                $page->addTag($topTag);
                $page->addTag($subTag);
                $page->setActive(TRUE);
                $manager->persist($page);
            }
            $manager->flush();
        } else {
            echo "LoadPageData Deactivated\n";
        }
    }
    
    public function getOrder()
    {
        return 50;
    }
}