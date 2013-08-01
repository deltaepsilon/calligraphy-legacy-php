<?php

namespace CDE\ContentBundle\Form\Type;

use PhpOption\Option;
use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentMarkedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\ContentBundle\Entity\Comment',
        ));
    }
}
