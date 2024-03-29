<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\Budget\Data\Validation;

use App\Domain\Entity\ValueObject\PlanPeriodType;
use App\UI\Service\Validator\ValueObjectValidationFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;

class GetDataV1Form extends AbstractType
{
    public function __construct(private readonly ValueObjectValidationFactoryInterface $valueObjectValidationFactory)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'constraints' => [new NotBlank(), new Uuid()],
            ])
            ->add('periodStart', TextType::class, [
                'constraints' => [new NotBlank(), new DateTime("Y-m-d H:i:s")],
            ])
            ->add('periodType', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    $this->valueObjectValidationFactory->create(PlanPeriodType::class)
                ],
            ])
            ->add('numberOfPeriods', TextType::class, [
                'constraints' => [new NotBlank(), new GreaterThanOrEqual(1), new LessThanOrEqual(12)],
            ]);
    }
}
