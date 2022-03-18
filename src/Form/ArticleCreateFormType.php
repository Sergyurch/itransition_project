<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('text', TextareaType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('image_path', FileType::class, [
                'attr' => ['class' => 'p-5 w-100 bg-white border rounded-2'],
                'mapped' => false,
                'required' => true
            ])
            ->add('category', ChoiceType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label'],
                'choices'  => [
                    'Фильмы' => 'Фильмы',
                    'Книги' => 'Книги',
                    'Игры' => 'Игры'
                ]
            ])
            ->add('author_rating', ChoiceType::class, [
                'row_attr' => ['class' => 'mb-3 col-md-7'],
                'label_attr' => ['class' => 'form-label'],
                'choices'  => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
