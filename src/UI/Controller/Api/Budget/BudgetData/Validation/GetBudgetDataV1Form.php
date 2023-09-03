<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\Budget\BudgetData\Validation;

use Symfony\Component\Form\Form;
use App\Application\Budget\Dto\GetBudgetDataV1RequestDto;
use App\UI\Service\Validator\ValueObjectValidationFactoryInterface;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class GetBudgetDataV1Form extends AbstractType
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
        $builder->add('dateStart', TextType::class, [
            'constraints' => [new NotBlank(), new DateTime("Y-m-d H:i:s")]
        ])->add('dateEnd', TextType::class, [
            'constraints' => [new NotBlank(), new DateTime("Y-m-d H:i:s"), new Callback(function ($value, ExecutionContextInterface $context, $payload): void {
                $this->validateDateRange($value, $context, $payload);
            })]
        ]);
    }

    public function validateDateRange($value, ExecutionContextInterface $context, $payload): void
    {
        $dateEnd = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value)->getTimestamp();

        /** @var Form $form */
        $form = $context->getRoot();
        /** @var GetBudgetDataV1RequestDto $dto */
        $dto = $form->getData();
        $dateStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->dateStart)->getTimestamp();
        $dateEndLimit = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->dateStart)->modify('+3 months')->getTimestamp();

        if ($dateStart > $dateEnd) {
            $context->buildViolation('DateEnd < DateStart')
                ->atPath('dateEnd')
                ->addViolation();
            return;
        }

        if ($dateEnd > $dateEndLimit) {
            $context->buildViolation('Period more than 3 months')
                ->atPath('dateEnd')
                ->addViolation();
        }
    }
}
