<?php

namespace CDE\CartBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class ProductSubscriptionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('tags', 'entity', array(
                'class' => 'CDEContentBundle:Tag',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('l')->where('l.lvl = 0');
                    },    
            ))
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('days', 'integer', array('required' => TRUE))
            ->add('recurring', 'checkbox', array('required' => FALSE))
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
