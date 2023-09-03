<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository\Traits;

use App\Domain\Entity\ValueObject\Id;

trait DeleteEntityTrait
{
    /**
     * @inheritDoc
     */
    public function delete(Id $id): void
    {
        $item = $this->get($id);
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
