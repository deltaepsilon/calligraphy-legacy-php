<?php

namespace CDE\SubscriptionBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class AddTagsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
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
        return 'cde_subscription';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\SubscriptionBundle\Entity\Subscription',
        );
    }
}
