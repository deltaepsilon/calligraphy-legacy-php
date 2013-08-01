<?php

namespace CDE\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('username')
            ->add('email', 'email')
            ->add('commentEmail', 'checkbox', array('label' => 'Enable Comment Email Notification', 'required' => FALSE))
            ->add('enabled', 'checkbox', array('required' => FALSE))
            ->add('locked', 'checkbox', array('required' => FALSE))
            ->add('expiresAt', 'datetime', array('required' => FALSE))
            ->add('roles', 'choice', array('multiple' => TRUE, 'choices' => array('ROLE_USER' => 'User', 'ROLE_ADMIN' => 'Admin', 'ROLE_SUPER_ADMIN' => 'Super Admin')))
            ->add('credentialsExpireAt', 'datetime', array('required' => FALSE))
            ;
    }
    
    public function getName()
    {
        return 'cde_user';
    }
   public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CDE\UserBundle\Entity\User',
            /**
             *  disabled validation on update, because it insists on validating the password,
             * which we don't want the user to have enter 
             */
            // 'validation_groups' => 'Registration',
        ));
    }
}
