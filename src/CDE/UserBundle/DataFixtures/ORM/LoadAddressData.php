<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\UserBundle\Entity\Address;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAddressData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        if (1===1) {
            $addresses = array(
                'spike' => array(
                    'first'         => 'Chris',
                    'last'          => 'Esplin',
                    'phone'         => '801-683-9796',
                    'line1'         => '1065 W 530 N',
                    'line2'         => '',
                    'line3'         => '',
                    'city'          => 'Orem',
                    'state'         => 'UT', 
                    'code'          => '84057',
                    'country'       => 'USA',
                    'instructions'  => 'Do not ship to me.',
                ),
                'chris' => array(
                    'first'         => 'Chris',
                    'last'          => 'Esplin',
                    'phone'         => '801-602-300-9796',
                    'line1'         => '1065 W 530 N',
                    'line2'         => '',
                    'line3'         => '',
                    'city'          => 'Orem',
                    'state'         => 'UT', 
                    'code'          => '84057',
                    'country'       => 'USA',
                    'instructions'  => 'Do not ship to me.',
                ),
                'melissa' => array(
                    'first'         => 'Melissa',
                    'last'          => 'Esplin',
                    'phone'         => '801-755-5227',
                    'line1'         => '1065 W 530 N',
                    'line2'         => '',
                    'line3'         => '',
                    'city'          => 'Orem',
                    'state'         => 'UT', 
                    'code'          => '84057',
                    'country'       => 'USA',
                    'instructions'  => 'Do not ship to me.',
                ),
            );
            foreach ($addresses as $username => $attributes) {
                $user = $this->getReference($username);
                $address = new Address();
                $address->setUser($user);
                $address->setFirst($attributes['first']);
                $address->setLast($attributes['last']);
                $address->setPhone($attributes['phone']);
                $address->setLine1($attributes['line1']);
                $address->setLine2($attributes['line2']);
                $address->setLine3($attributes['line3']);
                $address->setCity($attributes['city']);
                $address->setState($attributes['state']);
                $address->setCode($attributes['code']);
                $address->setCountry($attributes['country']);
                $address->setInstructions($attributes['instructions']);
                $manager->persist($address);
            }
            $manager->flush();
        } else {
            echo "LoadAddressData Deactivated\n";
        }
    }
    
    public function getOrder()
    {
        return 90;
    }
}