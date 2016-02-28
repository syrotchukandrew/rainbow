<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use AppBundle\Form\FloorType;






class EstateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'Заголовок объекта недвижимости',
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Описание объекта недвижимости',
                'attr' => [
                    'placeholder' => 'Добавте описание здесь',
                    'class' => 'form-control',
                    'rows' => 5,
                    'cols' => 120,
                ]
            ))
            ->add('district', EntityType::class, array(
                'class' => 'AppBundle:District',
                'choice_label' => 'title',
                'label'    => 'Выберите район из выпадающего списка',
            ))
            ->add('category', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'label'    => 'Выберите категорию из выпадающего списка',
                'choice_label' => 'title',
                'query_builder' => function(\Gedmo\Tree\Entity\Repository\NestedTreeRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.lvl = :user')
                        ->setParameter('user', 1);
                }
            ))
            ->add('type', ChoiceType::class, array(
                'choices' => array(
                    'Дома' => 'Дома',
                    'Квартиры' => 'Квартиры',
                    'Участки' => 'Участки',
                    'Оренда жилья' => 'Оренда жилья',
                    'Коммерция' => 'Коммерция',
                ),
                'choices_as_values' => true,
            ))
            ->add('imageFile', FileType::class, array(
               'multiple' => true,
                'label'    => 'Добавте фото недвижимости',
                'required' => false
            ))
            ->add('rent', CheckboxType::class, array(
                'label'    => 'Этот эбъект для оренды?',
                'required' => false,
            ))
            ->add('exclusive', CheckboxType::class, array(
                'label' => 'Добавить в екслюзив?',
                'required' => false,
            ))
            ->add('floor', FloorType::class, array(
                'property_path' => 'floor',
                'label'    => 'Этажность для квартир во многоэтажках',
            ))
            ->add('price', MoneyType::class, array(
                'label'    => 'Цена в гривнах',
                'grouping' => true,
                'currency' => 'UAH',
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Estate',
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_estate_type';
    }
}
