<?php

namespace CDE\CartBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class CartProductType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('quantity', 'integer', array('label' => 'Qty'));
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'CDE\CartBundle\Entity\Product',
        );
    }
    public function getName()
    {
        return 'cde_cart_product';
    }
    
}
