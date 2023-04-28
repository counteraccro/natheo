<?php
/**
 * Formulaire pour mon compte
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Form\Admin\User;

use App\Entity\Admin\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('login')
            ->add('firstname')
            ->add('lastname')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
