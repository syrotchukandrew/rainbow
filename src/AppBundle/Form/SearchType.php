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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'choices' => $options['categories_choices'],
                'label' => 'Категория',
                'choice_label' => 'title',
            ))
            ->add('except_floor', CheckboxType::class, array(
                'label' => 'form.search.disable_floor',
                'required' => false,

            ))
            ->add('district', EntityType::class, array(
                'class' => 'AppBundle:District',
                'placeholder' => 'form.search.district',
                'choice_label' => 'title',
                'label' => 'Район',
                'required' => false,
            ))
            ->add('price', ChoiceType::class, array(
                    'placeholder' => 'form.search.price',
                    'label' => 'Цена',
                    'choices' => array(
                        'form.search.to_20000' => 'to_20000',
                        'form.search.to_50000' => 'to_50000',
                        'form.search.more_then_50000' => 'more_then_50000',
                    ),
                    'choices_as_values' => true,
                    'required' => false,
                )
            )
            ->add('search', SubmitType::class, ['label' => 'form.search.search',
                'attr' => ['class' => 'btn btn-default']
            ]);
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