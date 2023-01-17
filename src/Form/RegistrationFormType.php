<?php

namespace App\Form;

use App\Entity\AnswerTypes;
use App\Entity\Countries;
use App\Entity\Hdm;
use App\Entity\User;
use Doctrine\DBAL\Types\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('POST');
        $builder
            ->add('email', EmailType::class, [
                'help' => 'Vaše e-mailová adresa ...',
                'label' => 'example@gmail.com',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-7 form-group bmd-form-group float-left',
                ]
            ])
            ->add('firstName', TextType::class, [
                'help' => 'Vaše jméno a příjmení ...',
                'label' => 'Jan Novák',
                'required' => true,
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-5 form-group bmd-form-group float-left',
                ],
            ])
            ->add('nickname', TextType::class, [
                'help' => 'Nickname (Přihlašovací jméno) ...',
                'label' => 'GhiMaster',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-4 form-group bmd-form-group float-left',
                ],
            ])
            /*
            ->add('title', null, [
                'label' => 'Osobní údaje',
                'label_attr' => [
                    'class' => "subtitle form-label bmd-label-floating"
                ],
                'disabled' => true,
                'row_attr' => [
                    'class' => 'col-md-12 form-group bmd-form-group float-left',
                ],
            ])
            */
            ->add('birthdate', DateType::class, [
                'help' => 'Datum narození ...',
                'label' => '.',
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-4 form-group bmd-form-group float-left',
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Muž' => 'Muž',
                    'Žena' => 'Žena',
                    'Jiné' => 'Jiné',
                ],
                'help' => 'Pohlaví ...',
                'label' => false,
                'attr' => ['class' => 'custom-select'],
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-4 form-group bmd-form-group float-left',
                ],
            ])
            ->add('password', PasswordType::class, [
                'help' => 'Přihlašovací heslo ...',
                'label' => 'SiLnÉ_h3sL0698',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-6 form-group bmd-form-group float-left',
                ],
            ])
            ->add('password_repeat', PasswordType::class, [
                'help' => 'Heslo znovu ...',
                'label' => 'SiLnÉ_h3sL0698',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-6 form-group bmd-form-group float-left',
                ],
            ])
            ->add('country', EntityType::class, [
                'class' => Countries::class,
                'help' => 'Vyberte svojí zemi ...',
                'label' => false,

                    'attr' => ['class' => 'custom-select'],
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-3 form-group bmd-form-group float-left',
                ],
                'choice_label' => function(Countries $countries) {
                    return sprintf('%s', $countries->getName());
                },
            ])
            ->add('hdm', EntityType::class, [
                'class' => Hdm::class,
                'help' => 'Vyberte svůj headset ...',
                'label' => false,

                'attr' => ['class' => 'custom-select'],
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-4 form-group bmd-form-group float-left',
                ],
                'choice_label' => function(Hdm $hdm) {
                    return sprintf('%s', $hdm->getName());
                },
            ])
            ->add('gdpr', ChoiceType::class, [
                'choices' => [
                    'Souhlasím' => true,
                    'Nesouhlasím' => false,
                ],
                'help' => 'Se zpracováním osobních údajů v rámci GDPR ...',
                'label' => false,
                'attr' => ['class' => 'custom-select'],
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-5 form-group bmd-form-group float-left',
                ],
            ])
            ->add('twitchNickname', TextType::class, [
                'help' => 'Twitch nickname ...',
                'label' => 'uzivatel [nepovinné]',
                'required' => false,
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-3 form-group bmd-form-group float-left',
                ],
            ])
            ->add('scoresaberId', NumberType::class, [
                'help' => 'Scoresaber ID ...',
                'label' => '76561198143704479 [nepovinné]',
                'required' => false,
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-5 form-group bmd-form-group float-left',
                ],
            ])
            ->add('discordNickname', TextType::class, [
                'help' => 'Discord nickname ...',
                'label' => 'uzivatel#1234 [nepovinné]',
                'required' => false,
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-4 form-group bmd-form-group float-left',
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
