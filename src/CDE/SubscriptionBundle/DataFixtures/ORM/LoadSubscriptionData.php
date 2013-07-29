<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\SubscriptionBundle\Entity\Subscription;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSubscriptionData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        if (1===0) {
            $users = array(
                    'Chris',
                    'Melissa',
                    'Client',
                    // Unsubscribed user will be good for testing the subscription process
                    // 'Unsubscribed',
                );
                
            foreach ($users as $key => $username) {
                $user = $this->getReference($username);
                
                // Current fixtures are numbered 0-2.  Increase random number generator
                $product = $this->getReference('product_'.rand(0, 2));
                $subscription = new Subscription();
                $subscription->setUser($user);
                $subscription->setProduct($product);
                $subscription->setExpires(new \DateTime());
                $this->addReference('subscription_'.$key, $subscription);
                $manager->persist($subscription);
            }
    
            $manager->flush();
        } else {
            echo "LoadSubscriptionData Deactivated\n";
        }
    }
    
    public function getOrder()
    {
        return 30;
    }
}