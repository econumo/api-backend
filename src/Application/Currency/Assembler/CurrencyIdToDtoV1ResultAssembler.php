<?php

declare(strict_types=1);

namespace App\Application\Currency\Assembler;

use App\Application\Currency\Dto\CurrencyResultDto;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\CurrencyRepositoryInterface;

class CurrencyIdToDtoV1ResultAssembler
{
    public function __construct(private readonly CurrencyRepositoryInterface $currencyRepository)
    {
    }

    public function assemble(Id $currencyId): CurrencyResultDto
    {
        $currency = $this->currencyRepository->get($currencyId);
        $dto = new CurrencyResultDto();
        $dto->id = $currency->getId()->getValue();
        $dto->code = $currency->getCode()->getValue();
        $dto->name = $currency->getName();
        $dto->symbol = $currency->getSymbol();
        return $dto;
    }
}
