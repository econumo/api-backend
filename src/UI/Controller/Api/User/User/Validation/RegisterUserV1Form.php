<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\User\User\Validation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterUserV1Form extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, [
            'constraints' => [new NotBlank(), new Email(), new Length(['max' => 256])],
        ])->add('password', TextType::class, [
            'constraints' => [new NotBlank(), new Length(['min' => 5])],
        ])->add('name', TextType::class, [
            'constraints' => [new NotBlank(), new Length(['min' => 2])],
        ]);
    }
}
