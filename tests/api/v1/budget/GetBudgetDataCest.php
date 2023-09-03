<?php

declare(strict_types=1);

namespace App\Tests\api\v1\budget;

use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class GetBudgetDataCest
{
    private string $url = '/api/v1/budget/get-budget-data';

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function requestShouldReturn200ResponseCode(ApiTester $I): void
    {
        $I->amAuthenticatedAsJohn();
        $I->sendGET($this->url, ['dateStart' => '2021-08-01 00:00:00', 'dateEnd' => '2021-09-01 00:00:00']);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function requestShouldReturn400ResponseCode(ApiTester $I): void
    {
        $I->amAuthenticatedAsJohn();
        $I->sendGET($this->url, ['dateStart' => '2021-08-01 00:00:00', 'dateEnd' => '2022-09-01 00:00:00']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function requestShouldReturn401ResponseCode(ApiTester $I): void
    {
        $I->sendGET($this->url, ['dateStart' => '2021-08-01 00:00:00', 'dateEnd' => '2021-09-01 00:00:00']);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function requestShouldReturnResponseWithCorrectStructure(ApiTester $I): void
    {
        $I->amAuthenticatedAsJohn();
        $I->sendGET($this->url, ['dateStart' => '2021-08-01 00:00:00', 'dateEnd' => '2021-09-01 00:00:00']);
        $I->seeResponseMatchesJsonType($I->getRootResponseWithItemsJsonType());
        $I->seeResponseMatchesJsonType($I->getBudgetDataDtoJsonType(), '$.data.items[0]');
        $I->seeResponseMatchesJsonType($I->getBudgetDataReportDtoJsonType(), '$.data.items[0].budgets[0]');
    }
}
