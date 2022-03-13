<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\User\Currency\Validation;

use App\UI\Service\Validator\ValueObjectValidationFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateCurrencyV1Form extends AbstractType
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
        $builder->add('currency', TextType::class, [
            'constraints' => [new NotBlank(), new Currency()],
        ]);
    }
}
