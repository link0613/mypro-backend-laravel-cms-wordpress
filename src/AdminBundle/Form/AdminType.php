<?php

namespace AdminBundle\Form;

use RestBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = isset($options['data']) && $options['data'] instanceof User ? $options['data'] : null;

        $builder
            ->add('full_name', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('role', ChoiceType::class, [
                'attr' => ['class' => 'form-control'],
                'choices'  => [
                    'ROLE_ADMIN_MANAGER' => 'Admin Manager',
                    'ROLE_ADMIN' => 'Admin',
                    'ROLE_MANAGER_BLOG' => 'Blog Manager'
                ]
            ]);

        if (!$admin) {
            $builder
                ->add('password', RepeatedType::class, [
                    'required' => false,
                    'type' => PasswordType::class,
                    'options' => ['attr' => ['class' => 'form-control']],
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ]);
        }

        $builder
            ->add('submit', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => ['class' => 'btn btn-primary center-block']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin_bundle_admin_type';
    }
}
