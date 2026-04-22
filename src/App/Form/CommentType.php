<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 07.03.16
 * Time: 15:38
 */

declare(strict_types=1);

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, array('label' => false,
            ));
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Comment::class,
        ));
    }
    public function getBlockPrefix(): string
    {
        return 'app_bundle_comment_type';
    }
}
