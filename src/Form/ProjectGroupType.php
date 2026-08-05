<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ProjectGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'empty_data' => '',
                'label' => 'Title',
            ])
            ->add('imagePath', TextType::class, [
                'label' => 'Image path',
                'required' => false,
                'help' => 'Optional, relative to public/, e.g. img/me.jpeg',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort order',
                'help' => 'Lower numbers appear first.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectGroup::class,
        ]);
    }
}
