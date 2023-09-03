<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\Budget\BudgetData;

use App\Application\Budget\BudgetDataService;
use App\Application\Budget\Dto\GetBudgetDataV1RequestDto;
use App\UI\Controller\Api\Budget\BudgetData\Validation\GetBudgetDataV1Form;
use App\Application\Exception\ValidationException;
use App\Domain\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UI\Service\Validator\ValidatorInterface;
use App\UI\Service\Response\ResponseFactory;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class GetBudgetDataV1Controller extends AbstractController
{
    public function __construct(private readonly BudgetDataService $budgetDataService, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * Get BudgetData
     *
     * @OA\Tag(name="Budget"),
     * @OA\Parameter(
     *     name="dateStart",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string"),
     *     description="Date from",
     * ),
     * @OA\Parameter(
     *     name="dateEnd",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string"),
     *     description="Date to",
     * ),
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         type="object",
     *         allOf={
     *             @OA\Schema(ref="#/components/schemas/JsonResponseOk"),
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="data",
     *                     ref=@Model(type=\App\Application\Budget\Dto\GetBudgetDataV1ResultDto::class)
     *                 )
     *             )
     *         }
     *     )
     * ),
     * @OA\Response(response=400, description="Bad Request", @OA\JsonContent(ref="#/components/schemas/JsonResponseError")),
     * @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/JsonResponseUnauthorized")),
     * @OA\Response(response=500, description="Internal Server Error", @OA\JsonContent(ref="#/components/schemas/JsonResponseException")),
     *
     *
     * @return Response
     * @throws ValidationException
     */
    #[Route(path: '/api/v1/budget/get-budget-data', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $dto = new GetBudgetDataV1RequestDto();
        $this->validator->validate(GetBudgetDataV1Form::class, $request->query->all(), $dto);
        /** @var User $user */
        $user = $this->getUser();
        $result = $this->budgetDataService->getBudgetData($dto, $user->getId());

        return ResponseFactory::createOkResponse($request, $result);
    }
}
