<?php

declare(strict_types=1);

namespace App\Application\Connection;

use App\Application\Connection\Dto\GetConnectionListV1RequestDto;
use App\Application\Connection\Dto\GetConnectionListV1ResultDto;
use App\Application\Connection\Assembler\GetConnectionListV1ResultAssembler;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Service\Connection\ConnectionAccountServiceInterface;
use App\Domain\Service\Connection\ConnectionServiceInterface;

class ConnectionListService
{
    public function __construct(private readonly GetConnectionListV1ResultAssembler $getConnectionListV1ResultAssembler, private readonly ConnectionServiceInterface $connectionService, private readonly ConnectionAccountServiceInterface $connectionAccountService)
    {
    }

    public function getConnectionList(
        GetConnectionListV1RequestDto $dto,
        Id $userId
    ): GetConnectionListV1ResultDto {
        $receivedAccountAccess = $this->connectionAccountService->getReceivedAccountAccess($userId);
        $issuedAccountAccess = $this->connectionAccountService->getIssuedAccountAccess($userId);
        $connectedUsers = $this->connectionService->getUserList($userId);
        return $this->getConnectionListV1ResultAssembler->assemble($dto, $userId, $receivedAccountAccess, $issuedAccountAccess, $connectedUsers);
    }
}
