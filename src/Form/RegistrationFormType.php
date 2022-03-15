<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('profile_image', FileType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label'],
                'mapped' => false,
                'required' => false
            ])
            ->add('full_name', TextType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label'],
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label'],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
