<?php

declare(strict_types=1);

namespace App\Tests\api\v1\budget;

use Codeception\Exception\ModuleException;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class AcceptAccessCest
{
    private string $url = '/api/v1/budget/accept-access';

    /**
     * @throws ModuleException
     */
    public function requestShouldReturn200ResponseCode(ApiTester $I): void
    {
        $I->amAuthenticatedAsDany();
        $I->sendPOST($this->url, ['planId' => '3a6d84be-d074-4a14-ab9a-86dfb083c91d']);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @throws ModuleException
     */
    public function requestShouldReturn400ResponseCode(ApiTester $I): void
    {
        $I->amAuthenticatedAsDany();
        $I->sendPOST($this->url, ['unexpected_param' => 'test']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * @throws ModuleException
     */
    public function requestShouldReturn401ResponseCode(ApiTester $I): void
    {
        $I->sendPOST($this->url, ['planId' => '3a6d84be-d074-4a14-ab9a-86dfb083c91d']);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * @throws ModuleException
     */
    public function requestShouldReturnResponseWithCorrectStructure(ApiTester $I): void
    {
        $I->amAuthenticatedAsDany();
        $I->sendPOST($this->url, ['planId' => '3a6d84be-d074-4a14-ab9a-86dfb083c91d']);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'item' => $I->getPlanDtoJsonType(),
            ],
        ]);
        $I->seeResponseMatchesJsonType($I->getPlanSharedAccessDtoJsonType(), '$.data.item.sharedAccess[0]');
    }
}
