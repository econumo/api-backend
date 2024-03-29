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

    public function getBalanceAnalyticsDtoJsonType(): array
    {
        return [
            'date' => 'string',
            'amount' => 'string'
        ];
    }

    public function getPlanDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'position' => 'integer|null',
            'createdAt' => 'string',
            'updatedAt' => 'string',
            'sharedAccess' => 'array',
        ];
    }

    public function getPlanSharedAccessDtoJsonType(): array
    {
        return [
            'user' => $this->getUserDtoJsonType(),
            'role' => 'string',
            'isAccepted' => 'integer',
        ];
    }

    public function getPlanFolderDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'position' => 'integer',
        ];
    }

    public function getPlanEnvelopeDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'icon' => 'string',
            'type' => 'string',
            'currencyId' => 'string',
            'folderId' => 'string|null',
            'position' => 'integer',
            'isArchived' => 'integer',
        ];
    }

    public function getPlanEnvelopeCategoryDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'type' => 'string',
            'icon' => 'string',
            'isArchived' => 'integer',
            'envelopeId' => 'string',
        ];
    }

    public function getPlanEnvelopeTagDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'isArchived' => 'integer',
            'envelopeId' => 'string',
        ];
    }

    public function getDetailedPlanDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'ownerUserId' => 'string',
            'name' => 'string',
            'createdAt' => 'string',
            'updatedAt' => 'string',
            'currencies' => 'array',
            'folders' => 'array',
            'envelopes' => 'array',
            'categories' => 'array',
            'tags' => 'array',
            'sharedAccess' => 'array',
        ];
    }

    public function getPlanDataDtoJsonType(): array
    {
        return [
            'periodStart' => 'string',
            'periodEnd' => 'string',
            'balances' => 'array',
            'exchanges' => 'array',
            'currencyRates' => 'array',
            'envelopes' => 'array',
            'categories' => 'array',
            'tags' => 'array',
        ];
    }

    public function getPlanDataBalanceDtoJsonType(): array
    {
        return [
            'currencyId' => 'string',
            'startBalance' => 'float|integer|null',
            'endBalance' => 'float|integer|null',
            'currentBalance' => 'float|integer|null',
            'income' => 'float|integer|null',
            'expenses' => 'float|integer|null',
            'exchanges' => 'float|integer|null',
            'hoards' => 'float|integer|null'
        ];
    }

    public function getPlanDataExchangeDtoJsonType(): array
    {
        return [
            'currencyId' => 'string',
            'budget' => 'float|integer',
            'amount' => 'float|integer',
        ];
    }

    public function getPlanDataCurrencyRateDtoJsonType(): array
    {
        return [
            'currencyId' => 'string',
            'baseCurrencyId' => 'string',
            'rate' => 'float|integer',
            'date' => 'string'
        ];
    }

    public function getPlanDataEnvelopeDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'budget' => 'float|integer',
            'available' => 'float|integer|null'
        ];
    }

    public function getPlanDataCategoryDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'currencyId' => 'string',
            'amount' => 'float|integer'
        ];
    }

    public function getPlanDataTagDtoJsonType(): array
    {
        return [
            'id' => 'string',
            'currencyId' => 'string',
            'amount' => 'float|integer'
        ];
    }
}
