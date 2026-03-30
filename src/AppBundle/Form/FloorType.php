<?php

declare(strict_types=1);

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FloorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('floor', IntegerType::class, array(
                'required' => false,
                'label'    => 'form.floor.floor',

            ))
            ->add('count_floor', IntegerType::class, array(
                'required' => false,
                'label'    => 'form.floor.count_floor',

            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {

    }

    public function getBlockPrefix(): string
    {
        return 'app_bundle_floor_type';
    }
}
