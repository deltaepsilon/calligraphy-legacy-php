<?php

namespace CDE\SubscriptionBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'entity', array('class' => 'CDEUserBundle:User'))
            ->add('product', 'entity', array(
                'class' => 'CDECartBundle:Product',
                'label' => 'Product',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('l')->where("l.type = 'subscription'");
                },
                ))
            ->add('expires')
            ;
    }
    
    public function getName()
    {
        return 'cde_subscription';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'CDE\SubscriptionBundle\Entity\Subscription',
        ));
    }
}
