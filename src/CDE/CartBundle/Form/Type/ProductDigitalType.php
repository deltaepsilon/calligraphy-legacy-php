<?php

namespace CDE\CartBundle\Form\Type;

use PhpOption\Option;
use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductDigitalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('uri', 'url', array('required' => TRUE))
            ->add('active', 'checkbox', array('required' => FALSE))
            ->add('keyImage')
            ->add('images', 'collection', array(
                'type' => 'text',
                'options' => array(
                    'required' => FALSE,
                ),
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => TRUE,
            ))
            ;
    }
    
    public function getName()
    {
        return 'cde_product';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\CartBundle\Entity\Product',
        ));
    }
}
