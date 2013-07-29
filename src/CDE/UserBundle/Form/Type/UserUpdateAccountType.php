<?php

namespace CDE\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;

class UserUpdateAccountType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'Update Email'))
            ->add('commentEmail', 'checkbox', array('label' => 'Enable Comment Notification Emails', 'required' => FALSE))
            ->add('new', 'repeated', array(
                'type' => 'password',
                'first_name' => 'new_password',
                'second_name' => 'verification',
                'required' => FALSE,
            ))
            ->add('current', 'password', array('label' => 'Current Password',))
            ;
    }
    
    public function getName()
    {
        return 'cde_user';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'CDE\UserBundle\Form\Model\UpdateUser',
            'validation_groups' => 'Registration',
        );
    }
}
