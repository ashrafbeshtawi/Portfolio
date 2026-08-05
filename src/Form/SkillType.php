<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Skill;
use App\Entity\SkillCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'label' => 'Name',
            ])
            ->add('category', EntityType::class, [
                'class' => SkillCategory::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'placeholder' => 'Choose a category',
            ])
            ->add('url', UrlType::class, [
                'label' => 'Link (optional)',
                'required' => false,
                'default_protocol' => 'https',
                'help' => 'E.g. a certificate verification URL.',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Shown on certification cards (unused for regular skills).',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort order',
                'help' => 'Lower numbers appear first within the category.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class,
        ]);
    }
}
