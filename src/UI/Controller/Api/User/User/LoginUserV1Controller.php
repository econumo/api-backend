<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\User\User;

use App\Application\User\UserService;
use App\Application\User\Dto\LoginUserV1RequestDto;
use App\Domain\Entity\User;
use App\UI\Controller\Api\User\User\Validation\LoginUserV1Form;
use App\Application\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UI\Service\Validator\ValidatorInterface;
use App\UI\Service\Response\ResponseFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class LoginUserV1Controller extends AbstractController
{
    public function __construct(private readonly UserService $userService, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * Login User
     *
     * @OA\Tag(name="User"),
     * @OA\Post(
     *     security={},
     *     @OA\RequestBody(@OA\JsonContent(ref=@Model(type=\App\Application\User\Dto\LoginUserV1RequestDto::class))),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="object",
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/JsonResponseOk"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         ref=@Model(type=\App\Application\User\Dto\LoginUserV1ResultDto::class)
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request", @OA\JsonContent(ref="#/components/schemas/JsonResponseError")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/JsonResponseUnauthorized")),
     *     @OA\Response(response=500, description="Internal Server Error", @OA\JsonContent(ref="#/components/schemas/JsonResponseException")),
     * )
     *
     *
     * @return Response
     * @throws ValidationException
     */
    #[Route(path: '/api/v1/user/login-user', methods: ['POST'])]
    public function __invoke() : Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException('Auth failed');
        }

        $result = $this->userService->loginUser($user);
        return new JsonResponse($result);
    }
}
