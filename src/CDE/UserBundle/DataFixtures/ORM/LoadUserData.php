<?php

namespace CDE\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use CDE\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        if (1===1) {
            $userManager = $this->container->get('fos_user.user_manager');
            $users = array(
                    'spike' => array(
                                'password' => 'alwaysuseapassphrase',
                                'email' => 'admin@melissaesplin.com',
                                'role' => 'ROLE_SUPER_ADMIN',
                               ),
                    'chris' => array(
                                'password' => 'alwaysuseapassphrase',
                                'email' => 'chris@christopher.com',
                                'role' => 'ROLE_ADMIN',
                               ),
                    'melissa' => array(
                                'password' => 'alwaysuseapassphrase',
                                'email' => 'melissa@melissaesplin.com',
                                'role' => 'ROLE_ADMIN',
                               ),
                );
            foreach ($users as $username => $attributes) {
                $user = $userManager->createUser();
                $user->setUsername($username);
                $user->setPlainPassword($attributes['password']);
                $user->setEmail($attributes['email']);
                if ($attributes['role'] === 'ROLE_SUPER_ADMIN') {
                    $user->setSuperAdmin(TRUE);
                } else if ($attributes['role'] === 'ROLE_ADMIN') {
                    $user->setAdmin(TRUE);
                }
                $user->setEnabled(TRUE);
                $userManager->updateUser($user);
                $this->addReference($username, $user);
            }
            $manager->flush();
        } else {
            echo "LoadUserData Deactivate\n";
        }
    }
    
    public function getOrder()
    {
        return 10;
    }
}