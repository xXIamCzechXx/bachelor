<?php

namespace App\Form;

use App\Entity\AnswerTypes;
use App\Entity\FormAnswers;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContactFormType extends AbstractType
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->token->getToken() ? $this->token->getToken()->getUser() : null;
        $builder
            ->add('name', TextType::class, [
                'help' => 'Vaše jméno a příjmení ...',
                'attr' => [ 'value' => $user ? $user->getFirstName() : '' ],
                'label' => 'Jan Novák',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-5 form-group bmd-form-group float-left',
                ],
            ])
            ->add('email', EmailType::class, [
                'help' => 'Vaše e-mailová adresa ...',
                'label' => 'example@gmail.com',
                    'attr' => [ 'value' => $user ? $user->getEmail() : '' ],
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-7 form-group bmd-form-group float-left',
                ]
            ])
            ->add('answerTypes', EntityType::class, [
                'class' => AnswerTypes::class,
                'help' => 'Požadavek ...',
                'label' => false,
                    'attr' => ['class' => 'custom-select'],
                    'label_attr' => [
                            'class' => "form-label bmd-label-floating"
                    ],
                    'row_attr' => [
                            'class' => 'col-md-8 form-group bmd-form-group float-left',
                    ],
                'choice_label' => function(AnswerTypes $answerTypes) {
                    return sprintf('%s', $answerTypes->getName());
                },
            ])
            ->add('phone', TelType::class, [
                'help' => 'Vaše telefonní číslo ...',
                'label' => '773 777 777',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-4 form-group bmd-form-group float-left',
                ]
            ])
            ->add('content', TextareaType::class, [
                'help' => 'Požadavek, který chcete, abychom řešili ...',
                'label' => 'Měl/a bych zájem o ...',
                'label_attr' => [
                    'class' => "form-label bmd-label-floating"
                ],
                'row_attr' => [
                    'class' => 'col-md-12 form-group bmd-form-group float-left',
                ],
                'attr' => [
                    'placeholder' => ''
                ]
            ])
            ->add('gdpr', CheckboxType::class, [
                'label' => 'Odškrtnutím souhlasíte se zpracováním osobních údajů',
                'mapped' => false,
                'label_attr' => [
                        'class' => "form-label"
                ],
                'row_attr' => [
                        'class' => 'col-md-12 form-group float-left',
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FormAnswers::class
        ]);
    }

}