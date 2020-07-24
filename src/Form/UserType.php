<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //Sets a different constraint for password to allow update user without updating password
        if (isset($options['update']) && $options['update'] === true) {
            $passwordNotBlank = [];
            $passwordRequired = false;
        } else {
            $passwordNotBlank = [
                    new NotBlank(['message' => 'Vous devez saisir un mot de passe.'])
                    ];
            $passwordRequired = true;
        }

        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                    'required' => $passwordRequired,
                    'first_options'  => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
                    'constraints' => array_merge(
                        [
                            new Length([
                                'min' => 6,
                                'minMessage' => 'Le mot de passe est trop court',
                                'max' => 12,
                                'maxMessage' => 'Le mot de passe est trop long'
                            ])
                        ],
                        $passwordNotBlank
                    )
                ]
            )
            ->add('email', EmailType::class, ['label' => 'Adresse email'])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER'
                ],
                'expanded' => true,
                'multiple' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id'   => 'pagination',
            'sortFieldList'=>[],
            'filterFieldList'=>[],
            'attr'=>['id'=>'pagination'],
            'update'=>false
        ]);
    }
}
