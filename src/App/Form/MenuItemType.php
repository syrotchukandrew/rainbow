<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.03.16
 * Time: 0:08
 */

declare(strict_types=1);

namespace App\Form;

use App\Entity\MenuItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, array(
                'attr' => array('autofocus' => true,),
                'label' => 'form.menu_item.title',
            ))
            ->add('description', TextareaType::class);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => MenuItem::class,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'app_bundle_menu_item_type';
    }
}