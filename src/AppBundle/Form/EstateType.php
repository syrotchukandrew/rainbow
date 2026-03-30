<?php

declare(strict_types=1);

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use AppBundle\Form\FloorType;

class EstateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $estate = $builder->getData();
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'form.estate.title',
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'form.estate.description',
                'attr' => [
                    'placeholder' => 'form.estate.description_placeholder',
                    'class' => 'form-control',
                    'rows' => 5,
                    'cols' => 120,
                ]
            ))
            ->add('district', EntityType::class, array(
                'class' => 'AppBundle\Entity\District',
                'choice_label' => 'title',
                'label' => 'form.estate.district',
            ))
            ->add('category', EntityType::class, array(
                'class' => 'AppBundle\Entity\Category',
                'choices' => $options['categories_choices'],
                'label' => 'form.estate.category',
                'choice_label' => 'title',
            ))
            ->add('imageFile', FileType::class, array(
                'multiple' => true,
                'label' => 'form.estate.image_file',
                'required' => false,
            ))
            ->add('exclusive', CheckboxType::class, array(
                'label' => 'form.estate.exclusive',
                'required' => false,
            ))
            ->add('floor', FloorType::class, array(
                'property_path' => 'floor',
                'label' => 'form.estate.floor',
            ))
            ->add('price', MoneyType::class, array(
                'label' => 'form.estate.price',
                'grouping' => true,
                'currency' => 'USD',
            ));

        if ($options['isDeleteImages']) {
            $builder
                ->add('files', EntityType::class, array(
                    'class' => 'AppBundle\Entity\File',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($estate) {
                        return $repository->createQueryBuilder('file')
                            ->where('file.estate = ?1')
                            ->setParameter(1, $estate);
                    },
                    'multiple' => true,
                    'choice_label' => 'id',
                    'label' => 'form.estate.files',
                    'required' => false,
                    'expanded' => true,
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Estate',
            'categories_choices' => null,
            'isDeleteImages' => null,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'app_bundle_estate_type';
    }
}
