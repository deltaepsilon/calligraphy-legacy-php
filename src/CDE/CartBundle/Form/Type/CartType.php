<?php

namespace CDE\CartBundle\Form\Type;

use CDE\CartBundle\Form\Type\CartProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\Collection;

class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('products', 'collection', array(
            'type' => new CartProductType(),
            'options' => array(
                'required' => FALSE
            )))
        ;
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\CartBundle\Entity\Cart',
        ));
    }
    public function getName()
    {
        return 'cde_cart';
    }
}
