<?php

namespace CDE\ContentBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active', 'checkbox', array('required' => FALSE))
            ->add('name')
            ->add('parent', 'entity', array(
                'required' => FALSE,
                'label' => 'Parent Tag',
                'class' => 'CDE\ContentBundle\Entity\Tag',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('l')->where('l.lvl = 0');
                },
            ))
            ;
    }
    
    public function getName()
    {
        return 'cde_tag';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\ContentBundle\Entity\Tag',
        ));
    }
}
