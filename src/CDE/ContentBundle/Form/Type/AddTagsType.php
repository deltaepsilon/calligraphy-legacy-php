<?php

namespace CDE\ContentBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class AddTagsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $parentTag = $options['parentTag']['tag'];
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
        return 'cde_page';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\ContentBundle\Entity\Page',
            'parentTag' => array(),
        );
    }
}
