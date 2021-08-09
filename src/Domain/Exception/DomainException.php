<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException as BaseDomainException;

class DomainException extends BaseDomainException implements DomainExceptionInterface
{

}
