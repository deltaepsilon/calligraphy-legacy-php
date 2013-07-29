<?php

namespace CDE\CartBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
        ->add('processed', 'checkbox', array(
            'required' => FALSE,
            ))
        ;
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\CartBundle\Entity\Transaction',
        );
    }
    public function getName()
    {
        return 'cde_transaction';
    }
}
