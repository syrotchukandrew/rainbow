<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.03.16
 * Time: 0:08
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'Название меню',
            ))
            ->add('description', TextareaType)

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