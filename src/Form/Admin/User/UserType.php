<?php
/**
 * Formulaire pour modifier un utilisateur
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Form\Admin\User;

use App\Entity\Admin\System\User;
use App\Form\AppFormType;
use App\Utils\System\User\Role;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AppFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('user.form_update.adresse_email.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.adresse_email.help', domain: 'user'),
                'required' => true,
            ])
            ->add('login', TextType::class, [
                'label' => $this->translator->trans('user.form_update.login.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.login.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_update.login.placeholder', domain: 'user'),
                ],
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => $this->translator->trans('user.form_update.firstname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.firstname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_update.firstname.placeholder', domain: 'user'),
                ],
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => $this->translator->trans('user.form_update.lastname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.lastname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_update.lastname.placeholder', domain: 'user'),
                ],
                'required' => false,
            ]);

        if (!$options['is_super_adm']) {
            $roles = Role::getListRole();
            $rolesTrans = [];
            foreach ($roles as $key => $role) {
                $rolesTrans[$this->translator->trans($key, domain: 'user')] = $role;
            }

            $builder->add('roles', ChoiceType::class, [
                'choices' => $rolesTrans,
                'label' => $this->translator->trans('user.form_update.role.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.role.help', domain: 'user'),
            ]);

            $builder->get('roles')->addModelTransformer(
                new CallbackTransformer(
                    function ($rolesArray) {
                        // transform the array to a string
                        return count($rolesArray) ? $rolesArray[0] : null;
                    },
                    function ($rolesString) {
                        // transform the string back to an array
                        return [$rolesString];
                    },
                ),
            );

            $builder->add('disabled', CheckboxType::class, [
                'label_attr' => ['class' => 'checkbox-switch'],
                'label' => $this->translator->trans('user.form_update.disabled.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.disabled.help', domain: 'user'),
                'required' => false,
            ]);
        }

        $icon = '<svg class="icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
  <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="2" d="M7 19H5a1 1 0 0 1-1-1v-1a3 3 0 0 1 3-3h1m4-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm7.441 1.559a1.907 1.907 0 0 1 0 2.698l-6.069 6.069L10 19l.674-3.372 6.07-6.07a1.907 1.907 0 0 1 2.697 0Z"/>
</svg>';
        $builder->add('save', SubmitType::class, [
            'label_html' => true,
            'label' => $icon . $this->translator->trans('user.form_update.submit', domain: 'user'),
            'attr' => [
                'class' => 'btn btn-primary btn-sm float-end',
                'div' => false,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_super_adm' => false,
        ]);
    }
}
