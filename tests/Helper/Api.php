<?php
namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Entity\ValueObject\Id;
use Ramsey\Uuid\Uuid;

class Api extends \Codeception\Module
{
    use AuthenticationTrait;
    use ContainerTrait;

    /**
     * @return \App\Domain\Entity\ValueObject\Id
     * @throws \Exception
     */
    public function generateId(): Id
    {
        $uuid = Uuid::uuid4();

        return new Id($uuid->toString());
    }

    public function getRootResponseWithItemsJsonType(): array
    {
        return [
            'data' => [
                'items' => 'array',
            ],
        ];
    }

    public function getTransactionDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'author' => $this->getUserDtoJsonType(),
            'type' => 'string',
            'accountId' => 'string',
            'accountRecipientId' => 'string|null',
            'amount' => 'float|integer',
            'amountRecipient' => 'float|integer|null',
            'categoryId' => 'string|null',
            'description' => 'string',
            'payeeId' => 'string|null',
            'tagId' => 'string|null',
            'date' => 'string',
        ];
    }

    public function getCurrentUserDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'email' => 'string',
            'avatar' => 'string',
            'options' => 'array',
            'currency' => 'string',
            'reportPeriod' => 'string',
        ];
    }

    public function getUserDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'avatar' => 'string',
        ];
    }

    public function getUserOptionDtoJsonType(): array
    {
        return [
            'name' => 'string',
            'value' => 'string|null',
        ];
    }

    public function getCategoryDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'position' => 'integer',
            'type' => 'string',
            'icon' => 'string',
            'isArchived' => 'integer',
            'createdAt' => 'string',
            'updatedAt' => 'string',
        ];
    }

    public function getPayeeDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'position' => 'integer|null',
            'isArchived' => 'integer',
            'createdAt' => 'string',
            'updatedAt' => 'string',
        ];
    }

    public function getTagDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'position' => 'integer|null',
            'isArchived' => 'integer',
            'createdAt' => 'string',
            'updatedAt' => 'string',
        ];
    }

    public function getAccountDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'owner' => $this->getUserDtoJsonType(),
            'folderId' => 'string|null',
            'name' => 'string',
            'position' => 'integer',
            'currency' => $this->getCurrencyDtoJsonType(),
            'balance' => 'float|integer',
            'type' => 'integer',
            'icon' => 'string',
            'sharedAccess' => 'array',
        ];
    }

    public function getSharedAccessDtoJsonType(): array
    {
        return [
            'user' => $this->getUserDtoJsonType(),
            'role' => 'string',
        ];
    }

    public function getAccountFolderDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'position' => 'integer',
            'isVisible' => 'integer',
        ];
    }

    public function getCurrencyDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'code' => 'string',
            'symbol' => 'string',
            'name' => 'string',
        ];
    }

    public function getCurrencyRateDtoJsonType(): array
    {
        return [
            'currencyId' => 'string',
            'baseCurrencyId' => 'string',
            'rate' => 'float',
            'updatedAt' => 'string',
        ];
    }

    public function getConnectionInviteDtoJsonType(): array
    {
        return [
            'code' => 'string',
            'expiredAt' => 'string',
        ];
    }

    public function getConnectionDtoJsonType(): array
    {
        return [
            'user' => $this->getUserDtoJsonType(),
            'sharedAccounts' => 'array',
        ];
    }

    public function getConnectionAccountAccessDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'role' => 'string',
        ];
    }

    public function getBudgetDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'icon' => 'string',
            'carryOver' => 'integer',
            'carryOverNegative' => 'integer',
            'carryOverStartDate' => 'string',
            'amount' => 'float|integer',
            'position' => 'integer',
            'owner' => 'array',
            'sharedAccess' => 'array',
            'categories' => 'array',
            'tags' => 'array',
            'excludeTags' => 'integer',
        ];
    }

    public function getBudgetDataDtoJsonType(): array
    {
        return [
            'dateStart' => 'string',
            'dateEnd' => 'string',
            'totalIncome' => 'float|integer',
            'totalExpense' => 'float|integer',
            'budgets' => 'array'
        ];
    }

    public function getBudgetDataReportDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'spent' => 'float|integer',
        ];
    }
}
