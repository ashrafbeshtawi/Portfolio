<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Project;
use App\Entity\ProjectGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'label' => 'Name',
            ])
            ->add('projectGroup', EntityType::class, [
                'class' => ProjectGroup::class,
                'choice_label' => 'title',
                'label' => 'Group',
                'placeholder' => 'Choose a group',
            ])
            ->add('url', UrlType::class, [
                'empty_data' => '',
                'label' => 'URL',
                'default_protocol' => 'https',
                'help' => 'Repository or live demo link.',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Short blurb shown on the project card.',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort order',
                'help' => 'Lower numbers appear first within the group.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
