<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'form.category.title',
            ));

        if ($options['isForm_cat']) {
            $builder
                ->add('parent', EntityType::class, array(
                    'required' => false,
                    'class' => 'App\Entity\Category',
                    'choice_label' => 'title',
                    'label' => 'form.category.parent',
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Category',
            'isForm_cat' => null,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'app_bundle_category_type';
    }
}
