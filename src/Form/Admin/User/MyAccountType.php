<?php
/**
 * Formulaire pour mon compte
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Form\Admin\User;

use App\Entity\Admin\System\User;
use App\Form\AppFormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MyAccountType extends AppFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('user.form.adresse_email.label', domain: 'user'),
                'help' => $this->translator->trans('user.form.adresse_email.help', domain: 'user'),
                'disabled' => true,
            ])
            ->add('login', TextType::class, [
                'label' => $this->translator->trans('user.form.login.label', domain: 'user'),
                'help' => $this->translator->trans('user.form.login.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form.login.placeholder', domain: 'user'),
                ],
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => $this->translator->trans('user.form.firstname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form.firstname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form.firstname.placeholder', domain: 'user'),
                ],
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => $this->translator->trans('user.form.lastname.label', domain: 'user'),
                'help' => $this->translator->trans('user.form.lastname.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form.lastname.placeholder', domain: 'user'),
                ],
                'required' => false,
            ])

            ->add('avatar', FileType::class, [
                'label' => $this->translator->trans('user.form.avatar.label', domain: 'user'),
                'help' => $this->translator->trans('user.form.avatar.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form.avatar.placeholder', domain: 'user'),
                ],
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File(
                        maxSize: '1024k',
                        extensions: ['gif', 'jpg', 'jpeg', 'png'],
                        extensionsMessage: $this->translator->trans('user.form.avatar.error.upload', domain: 'user'),
                    ),
                ],
            ])

            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('user.form.description.label', domain: 'user'),
                'help' => $this->translator->trans('user.form.description.help', domain: 'user'),
                'attr' => [
                    'placeholder' => $this->translator->trans('user.form.description.placeholder', domain: 'user'),
                ],
                'required' => false,
            ]);

        $icon =
            '<svg class="icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"></path></svg>';

        $builder->add('save', SubmitType::class, [
            'label_html' => true,
            'label' => $icon . $this->translator->trans('user.form.submit', domain: 'user'),
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
        ]);
    }
}
