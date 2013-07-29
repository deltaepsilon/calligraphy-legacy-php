<?php
// See https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_forms.md
namespace CDE\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use FOS\UserBundle\Model\UserInterface;

class RegistrationFormHandler extends BaseHandler
{
    protected function onSuccess(UserInterface $user, $confirmation)
    {
        parent::onSuccess($user, $confirmation);
    }
}
