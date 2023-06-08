<?php
/**
 * Formulaire pour modifier un utilisateur
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Form\Admin\User;

use App\Entity\Admin\User;
use App\Form\AppFormType;
use App\Utils\User\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AppFormType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('user.form_update.adresse_email.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.adresse_email.help', domain: 'user'),
                'required' => true
            ])
            ->add('login', TextType::class, [
                'label' => $this->translator->trans('user.form_update.login.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.login.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_update.login.placeholder', domain: 'user')
                ],
                'required' => false
            ])
            ->add('firstname', TextType::class, [
                'label' => $this->translator->trans('user.form_update.firstname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.firstname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_update.firstname.placeholder', domain: 'user')
                ],
                'required' => false

            ])
            ->add('lastname', TextType::class, [
                'label' => $this->translator->trans('user.form_update.lastname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.lastname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form_update.lastname.placeholder', domain: 'user')
                ],
                'required' => false
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

            $builder->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                    function ($rolesArray) {
                        // transform the array to a string
                        return count($rolesArray)? $rolesArray[0]: null;
                    },
                    function ($rolesString) {
                        // transform the string back to an array
                        return [$rolesString];
                    }
                ));

            $builder->add('disabled', CheckboxType::class, [
                'label_attr' => ['class' => 'checkbox-switch'],
                'label' => $this->translator->trans('user.form_update.disabled.label', domain: 'user'),
                'help' => $this->translator->trans('user.form_update.disabled.help', domain: 'user'),
                'required' => false,
            ]);
        }

        $builder->add('save', SubmitType::class, [
            'label' => $this->translator->trans('user.form_update.submit', domain: 'user'),
            'attr' => [
                'class' => 'btn-secondary'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_super_adm' => false
        ]);
    }
}
