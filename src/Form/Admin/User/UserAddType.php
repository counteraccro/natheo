<?php

namespace App\Form\Admin\User;

use App\Entity\Admin\System\User;
use App\Form\AppFormType;
use App\Utils\User\Role;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAddType extends AppFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('user.form_add.adresse_email.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_add.adresse_email.help', domain: 'user'),
                'required' => true
            ]);

        $roles = Role::getListRole();
        $rolesTrans = [];
        foreach ($roles as $key => $role) {
            $rolesTrans[$this->translator->trans($key, domain: 'user')] = $role;
        }
        $builder->add('roles', ChoiceType::class, [
            'choices' => $rolesTrans,
            'label' => $this->translator->trans('user.form_add.role.label', domain: 'user'),
            'help' => $this->translator->trans('user.form_add.role.help', domain: 'user'),
        ])
            ->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));

        $builder->add('login', TextType::class, [
            'label' => $this->translator->trans('user.form_add.login.label', domain: 'user'),
            'help' => $this->translator->trans('user.form_add.login.help', domain: 'user'),
            'attr' => [
                'placeholder' => $this->translator->trans('user.form_add.login.placeholder', domain: 'user')
            ],
            'required' => false
        ])
            ->add('firstname', TextType::class, [
                'label' => $this->translator->trans('user.form_add.firstname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_add.firstname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_add.firstname.placeholder', domain: 'user')
                ],
                'required' => false

            ])
            ->add('lastname', TextType::class, [
                'label' => $this->translator->trans('user.form_add.lastname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_add.lastname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_add.lastname.placeholder', domain: 'user')
                ],
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->translator->trans('user.form_add.submit', domain: 'user'),
                'attr' => [
                    'class' => 'btn-secondary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
