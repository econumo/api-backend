<?php

declare(strict_types=1);

namespace App\UI\Controller\Api\Account\Account;

use App\Application\Account\AccountService;
use App\Application\Account\Dto\UpdateAccountV1RequestDto;
use App\Domain\Entity\User;
use App\UI\Controller\Api\Account\Account\Validation\UpdateAccountV1Form;
use App\Application\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UI\Service\Validator\ValidatorInterface;
use App\UI\Service\Response\ResponseFactory;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UpdateAccountV1Controller extends AbstractController
{
    private AccountService $accountService;
    private ValidatorInterface $validator;

    public function __construct(AccountService $accountService, ValidatorInterface $validator)
    {
        $this->accountService = $accountService;
        $this->validator = $validator;
    }

    /**
     * Update Account
     *
     * @SWG\Tag(name="Account"),
     * @SWG\Tag(name="Need automation"),
     * @SWG\Parameter(
     *     name="payload",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=\App\Application\Account\Dto\UpdateAccountV1RequestDto::class)),
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="object",
     *         allOf={
     *             @SWG\Schema(ref="#/definitions/JsonResponseOk"),
     *             @SWG\Schema(
     *                 @SWG\Property(
     *                     property="data",
     *                     ref=@Model(type=\App\Application\Account\Dto\UpdateAccountV1ResultDto::class)
     *                 )
     *             )
     *         }
     *     )
     * ),
     * @SWG\Response(response=400, description="Bad Request", @SWG\Schema(ref="#/definitions/JsonResponseError")),
     * @SWG\Response(response=500, description="Internal Server Error", @SWG\Schema(ref="#/definitions/JsonResponseException")),
     *
     * @Route("/api/v1/account/update-account", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function __invoke(Request $request): Response
    {
        $dto = new UpdateAccountV1RequestDto();
        $this->validator->validate(UpdateAccountV1Form::class, $request->request->all(), $dto);
        /** @var User $user */
        $user = $this->getUser();
        $result = $this->accountService->updateAccount($dto, $user->getId());

        return ResponseFactory::createOkResponse($request, $result);
    }
}