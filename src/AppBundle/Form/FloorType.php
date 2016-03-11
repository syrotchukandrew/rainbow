<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FloorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('floor', IntegerType::class, array(
                'required' => false,
                'label'    => 'На каком этаже находится квартира',

            ))
            ->add('count_floor', IntegerType::class, array(
                'required' => false,
                'label'    => 'Сколько этажей во многоэтажке',

            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_floor_type';
    }
}
