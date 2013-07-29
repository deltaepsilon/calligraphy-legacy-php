<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\CartBundle\Entity\Discount;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDiscountData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        if (1===1) {
            $discount = new Discount();
            $discount->setCode('DONOTPAYFORANYTHING');
            $discount->setDescription('Eterna');
            $discount->setExpires(10000);
            $discount->setMaxUses(10000);
            $discount->setPercent(1);
            $manager->persist($discount);
            $manager->flush();
        } else {
            echo "LoadDiscountData Deactivated\n";
        }
    }
    
    public function getOrder()
    {
        return 80;
    }
}