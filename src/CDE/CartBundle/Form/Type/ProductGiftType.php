<?php

namespace CDE\CartBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;

class ProductGiftType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('discountValue', 'integer', array('label' => 'Discount Value'))
            ->add('discountPercent', 'percent', array('label' => 'Discount Percent (use whole numbers)'))
            ->add('expiration', 'integer', array('label' => 'Expiration in Days'))
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
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\CartBundle\Entity\Product',
        );
    }
}
