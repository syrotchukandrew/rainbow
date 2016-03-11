<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 10.03.16
 * Time: 12:20
 */


namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
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
            ->add('rent', CheckboxType::class, array(
                'label' => 'Исключить первый/последний этаж',
                'required' => false,
            ))
            ->add('exclusive', CheckboxType::class, array(
                'label' => 'Ексклюзив',
                'required' => false,
            ))
            ->add('price', RangeType::class, array(
                'label' => 'Цена',
                'attr' => array(
                    'min' => 5,
                    'max' => 50
            )));
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