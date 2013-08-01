<?php

namespace CDE\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('username')
            ->add('plainPassword', 'repeated', array('type' => 'password', 'first_name' => 'password', 'second_name' => 'verify_password'))
            ->add('email', 'email')
            ->add('enabled', 'checkbox', array('required' => FALSE))
            ->add('locked', 'checkbox', array('required' => FALSE))
            ->add('expiresAt', 'datetime', array(
                'required' => FALSE,
                'label' => 'Account Expiration',
            ))
            ->add('roles', 'choice', array('multiple' => TRUE, 'choices' => array('ROLE_USER' => 'User', 'ROLE_ADMIN' => 'Admin', 'ROLE_SUPER_ADMIN' => 'Super Admin')))
            ->add('credentialsExpireAt', 'datetime', array(
                'required' => FALSE,
                'label' => 'Credential Expiration'
            ))
            ;
    }
    
    public function getName()
    {
        return 'cde_user';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\UserBundle\Entity\User',
            'validation_groups' => 'Registration',
        ));
    }
}
