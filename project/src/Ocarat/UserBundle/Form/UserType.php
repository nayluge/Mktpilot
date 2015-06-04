<?php

namespace Ocarat\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UserType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array('attr' => array('class' => 'form-control')))
            ->add('firstName', 'text', array('attr' => array('class' => 'form-control')))
            ->add('lastName', 'text', array('attr' => array('class' => 'form-control')))
            ->add('email', 'text', array('attr' => array('class' => 'form-control')))
            ->add('enabled', 'checkbox', array('required' => false))
            ->add('locked', 'checkbox', array('required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ocarat\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ocarat_userbundle_user';
    }
}
