<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CookieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agreeTerms', CheckboxType::class, [
                    'label'    => 'Nezbytné pro používání webu', //
                    'required' => true,
                    'attr' => ['id' => ''],
                    'label_attr' => ['style' => 'padding-left: 0;'],
                    'row_attr' => ['id' => 'checklist', 'class' => 'mb-0 checklist'],
                    'disabled' => true,
                    'data' => true,
            ])
            ->add('agreeMarketingTerms', CheckboxType::class, [
                    'label'    => 'Volitelné pro zlepšování služeb',
                    'required' => false,
                    'attr' => ['id' => ''],
                    'label_attr' => ['style' => 'padding-left: 0;'],
                    'row_attr' => ['id' => 'checklist', 'class' => 'mb-0 checklist'],
                    'disabled' => false,
                    'data' => true,
            ])
            ->add('confirm', SubmitType::class, [
                'label' => 'Potvrdit všechny',
                    'row_attr' => [
                        'style' => 'float: left; margin: 0 !important; padding-bottom: 6px;'
                    ],
                    'attr' => [
                        'id' => 'agree_butt'
                    ]
                ])
            ->add('discard', ButtonType::class, [
                'label' => 'Nastavení',
                'row_attr' => [
                    'style' => 'float: left; margin: 0 !important; padding-bottom: 6px;'
                ],
                'attr' => [
                    'id' => 'to_settings_butt'
                ]
            ])
            //->getForm()
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
