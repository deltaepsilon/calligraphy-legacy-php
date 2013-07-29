<?php

namespace CDE\ClientBundle\Tests\Entity;

use CDE\UserBundle\Entity\User;


class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultPropertiesAreNull()
    {
        $user = new User();
        $defaults = array('id', 'username', 'usernameCanonical', 'email', 'emailCanonical', 'password', 'lastLogin');
        foreach ($defaults as $base) {
            $this->assertNull($user->{"get$base"}());
        }
        $this->assertEquals(TRUE, is_array($user->getRoles()));
    }
    
    public function testGettersAndSetters()
    {
        
    }
}
