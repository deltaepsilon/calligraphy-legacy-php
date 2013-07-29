<?php

namespace CDE\CartBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CartDiscountType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
        ->add('id', 'hidden')
        ->add('code', 'text', array(
            'label' => 'Add Discount Code to Cart',
            'required' => FALSE,
        ));
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'CDE\CartBundle\Entity\Discount',
            'validation_groups' => array('csrf_only'),
        );
    }
    public function getName()
    {
        return 'cde_cart_discount';
    }
    
}
