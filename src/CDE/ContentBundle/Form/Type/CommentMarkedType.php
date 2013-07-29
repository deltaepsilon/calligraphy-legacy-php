<?php

namespace CDE\ContentBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class CommentMarkedType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('marked', 'checkbox', array(
                'required' => FALSE,
                'label' => 'Mark As Read'
            ))
            ;
    }
    
    public function getName()
    {
        return 'cde_comment';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\ContentBundle\Entity\Comment',
        );
    }
}
