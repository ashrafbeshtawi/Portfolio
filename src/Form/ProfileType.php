<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'empty_data' => '',
                'label' => 'Full name',
            ])
            ->add('headline', TextType::class, [
                'empty_data' => '',
                'label' => 'Headline',
                'help' => 'Shown under your name in the hero section.',
            ])
            ->add('tagline', TextareaType::class, [
                'empty_data' => '',
                'label' => 'Tagline',
                'attr' => ['rows' => 3],
                'help' => 'Short pitch shown in the hero section.',
            ])
            ->add('email', EmailType::class, [
                'empty_data' => '',
                'label' => 'Email',
            ])
            ->add('githubUrl', UrlType::class, [
                'label' => 'GitHub URL',
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('linkedinUrl', UrlType::class, [
                'label' => 'LinkedIn URL',
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
            ])
            ->add('aboutText', TextareaType::class, [
                'label' => 'About me',
                'required' => false,
                'attr' => ['rows' => 6],
                'help' => 'Separate paragraphs with a blank line.',
            ])
            ->add('highlights', TextareaType::class, [
                'label' => 'Highlights',
                'required' => false,
                'attr' => ['rows' => 4],
                'help' => 'One highlight per line — shown as fact tiles in the About section.',
            ])
            ->add('contactHeading', TextType::class, [
                'empty_data' => '',
                'label' => 'Contact heading',
            ])
            ->add('contactText', TextareaType::class, [
                'label' => 'Contact text',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('photoPath', TextType::class, [
                'label' => 'Photo path',
                'required' => false,
                'help' => 'Relative to public/, e.g. img/me.jpeg',
            ])
            ->add('cvPath', TextType::class, [
                'label' => 'CV path',
                'required' => false,
                'help' => 'Relative to public/, e.g. cv.pdf — leave empty to hide the download button.',
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta description',
                'required' => false,
                'attr' => ['rows' => 2],
                'help' => 'Used for search engines and social previews.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
