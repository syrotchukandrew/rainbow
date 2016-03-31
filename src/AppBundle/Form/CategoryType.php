<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'Название категории',
            ));

        if ($options['isForm_cat']) {
            $builder
                ->add('parent', EntityType::class, array(
                    'required' => false,
                    'class' => 'AppBundle:Category',
                    'choice_translation_domain' => true,
                    'choice_label' => 'title',
                    'label' => 'Выберите родительскую категорию из выпадающего списка',
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Category',
            'isForm_cat' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_category_type';
    }
}
