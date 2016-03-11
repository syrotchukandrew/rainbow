<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 10.03.16
 * Time: 12:20
 */


namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $estate = $builder->getData();
        $builder
            ->add('category', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'choice_translation_domain' => true,
                'choices' => $options['categories_choices'],
                'label' => 'Выберите категорию из выпадающего списка',
                'choice_label' => 'title',
            ))
            ->add('district', EntityType::class, array(
                'class' => 'AppBundle:District',
                'placeholder' => 'Выберите район',
                'choice_label' => 'title',
                'label' => 'Выберите район из выпадающего списка',
            ))
            ->add('price', ChoiceType::class, array(
                    'placeholder' => 'Выберите цену',
                    'label' => 'Цена',
                    'choices' => array(
                        '0 - 20000' => 'to_20000',
                        '20001 - 50000' => 'to_50000',
                        'больше 50000' => 'more_then_50000',
                    ),
                    'choices_as_values' => true,
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(

            'categories_choices' => null,

        ));

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_search_type';
    }
}