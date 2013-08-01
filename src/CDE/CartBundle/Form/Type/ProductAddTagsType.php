<?php

namespace CDE\CartBundle\Form\Type;

use PhpOption\Option;
use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductAddTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tags', 'entity', array(
                'multiple' => TRUE,
                'expanded' => TRUE,
                'class' => 'CDE\ContentBundle\Entity\Tag',
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
