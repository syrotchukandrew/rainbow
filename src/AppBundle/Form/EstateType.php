<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Form\FileType as TypeFile;




class EstateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'Title',
            ))
            ->add('description', TextareaType::class, array(
                'attr' => [
                    'placeholder' => 'Add description',
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ))
           /* ->add('files', CollectionType::class, array(
                'entry_type'   => new TypeFile(),
                'allow_add'    => true,
            ))*/
            ->add('images', FileType::class, array(
                'data_class' => 'AppBundle\Entity\File',
                'required' => false,
                'attr' => array(
                    'multiple' => 'multiple',
                ),
                'mapped' => false,
            ))
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
