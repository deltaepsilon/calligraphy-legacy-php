<?php

namespace CDE\CartBundle\Form\Type;

use CDE\CartBundle\Form\Type\CartProductType;
use PhpOption\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\Collection;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('description')
        ->add('expires')
        ->add('maxUses')
        ->add('value')
        ->add('percent')
        ->add('product', 'entity', array('class' => 'CDE\CartBundle\Entity\Product', 'required' => FALSE))
        ;
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\CartBundle\Entity\Discount',
        ));
    }
    public function getName()
    {
        return 'cde_discount';
    }
}
