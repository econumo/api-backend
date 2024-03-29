<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\Connection\Invite;

use App\Application\Connection\InviteService;
use App\Application\Connection\Dto\GenerateInviteV1RequestDto;
use App\UI\Controller\Api\Connection\Invite\Validation\GenerateInviteV1Form;
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

class GenerateInviteV1Controller extends AbstractController
{
    public function __construct(private readonly InviteService $inviteService, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * Generate invite
     *
     * @OA\Tag(name="Connection"),
     * @OA\RequestBody(@OA\JsonContent(ref=@Model(type=\App\Application\Connection\Dto\GenerateInviteV1RequestDto::class))),
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
     *                     ref=@Model(type=\App\Application\Connection\Dto\GenerateInviteV1ResultDto::class)
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
    #[Route(path: '/api/v1/connection/generate-invite', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $dto = new GenerateInviteV1RequestDto();
        $this->validator->validate(GenerateInviteV1Form::class, $request->request->all(), $dto);
        /** @var User $user */
        $user = $this->getUser();
        $result = $this->inviteService->generateInvite($dto, $user->getId());

        return ResponseFactory::createOkResponse($request, $result);
    }
}
