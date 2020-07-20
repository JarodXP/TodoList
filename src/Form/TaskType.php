<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le titre ne doit pas dépasser 20 caractères'
                    ])
                ]
            ])
            ->add('content', TextareaType::class)
            //->add('author') ===> must be the user authenticated
        ;
    }
}
