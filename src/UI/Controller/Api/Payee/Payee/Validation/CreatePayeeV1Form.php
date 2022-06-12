<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\Payee\Payee\Validation;

use App\Domain\Entity\ValueObject\PayeeName;
use App\Infrastructure\Symfony\Form\Constraints\OperationId;
use App\UI\Service\Validator\ValueObjectValidationFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;

class CreatePayeeV1Form extends AbstractType
{
    private ValueObjectValidationFactoryInterface $valueObjectValidationFactory;

    public function __construct(ValueObjectValidationFactoryInterface $valueObjectValidationFactory)
    {
        $this->valueObjectValidationFactory = $valueObjectValidationFactory;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'constraints' => [new NotBlank(), new Uuid(), new OperationId()],
        ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => PayeeName::MAX_LENGTH, 'min' => PayeeName::MIN_LENGTH]),
                    $this->valueObjectValidationFactory->create(PayeeName::class)
                ],
            ])
            ->add('accountId', TextType::class, [
                'constraints' => [new Uuid()],
            ]);
    }
}
