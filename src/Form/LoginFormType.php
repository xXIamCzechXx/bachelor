<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Faker\Provider\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class, [
                'help' => 'Přihlašovací jméno / e-mailová adresa ...',
                'label' => false,
                'row_attr' => [
                    'class' => 'col-md-12 form-group bmd-form-group float-left',
                ]
            ])
            ->add('password', PasswordType::class, [
                'help' => 'Přihlašovací heslo ...',
                'label' => false,
                'row_attr' => [
                    'class' => 'col-md-12 form-group bmd-form-group float-left',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
