<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository\Traits;

use App\Domain\Entity\ValueObject\Id;
use Ramsey\Uuid\Uuid;

trait NextIdentityTrait
{
    /**
     * @inheritDoc
     */
    public function getNextIdentity(): Id
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $uuid = Uuid::uuid4();

        return new Id($uuid->toString());
    }
}
