<?php

namespace CDE\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('first', 'text', array('label' => 'First Name'))
            ->add('last', 'text', array('label' => 'Last Name'))
            ->add('phone')
            ->add('line1', 'text', array('label' => 'Address'))
            ->add('line2', 'text', array('required' => FALSE))
            ->add('line3', 'text', array('required' => FALSE))
            ->add('city')
            ->add('state')
            ->add('code')
            ->add('country', 'text', array('required' => FALSE))
            ->add('instructions', 'textarea', array('required' => FALSE, 'label' => 'Delivery Instructions'))
            ;
    }
    
    public function getName()
    {
        return 'cde_address';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\UserBundle\Entity\Address',
        );
    }
}
