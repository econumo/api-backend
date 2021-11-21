<?php
declare(strict_types=1);

namespace App\Application\Account\Dto;

use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     required={"id", "name", "position", "currencyId", "balance"}
 * )
 */
class AccountRequestDto
{
    /**
     * Id
     * @var string
     * @SWG\Property(example="a5e2eee2-56aa-43c6-a827-ca155683ea8d")
     */
    public string $id;

    /**
     * Account name
     * @var string
     * @SWG\Property(example="Cash")
     */
    public string $name;

    /**
     * Position
     * @var int
     * @SWG\Property(example="1")
     */
    public int $position;

    /**
     * Currency Id
     * @var string
     * @SWG\Property(example="77adad8a-9982-4e08-8fd7-5ef336c7a5c9")
     */
    public string $currencyId;

    /**
     * Current balance
     * @var float
     * @SWG\Property(example="13.07")
     */
    public float $balance;
}