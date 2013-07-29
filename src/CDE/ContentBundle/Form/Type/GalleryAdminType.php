<?php

namespace CDE\ContentBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class GalleryAdminType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('filename', 'file')
            ->add('user', 'entity', array(
                'class' => 'CDE\UserBundle\Entity\User',
            ))
            ->add('title')
            ->add('description')
            ;
    }
    
    public function getName()
    {
        return 'cde_gallery';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\ContentBundle\Entity\Gallery',
        );
    }
}
