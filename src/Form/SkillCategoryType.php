<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\SkillCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SkillCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'label' => 'Name',
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icon',
                'required' => false,
                'help' => 'An emoji shown next to the category name, e.g. ⚙️',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort order',
                'help' => 'Lower numbers appear first.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SkillCategory::class,
        ]);
    }
}
