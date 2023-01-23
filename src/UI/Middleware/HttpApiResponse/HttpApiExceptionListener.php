<?php

declare(strict_types=1);

namespace App\UI\Middleware\HttpApiResponse;

use App\Application\Exception\AccessDeniedException;
use App\Application\Exception\ValidationException;
use App\UI\Service\Response\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class HttpApiExceptionListener
{
    /**
     * @throws \Throwable
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($event->getRequest() instanceof Request) {
            if ($exception instanceof ValidationException) {
                $response = ResponseFactory::createErrorResponse(
                    $event->getRequest(),
                    $exception->getMessage(),
                    (int) $exception->getCode(),
                    $exception->getErrors()
                );
            } elseif ($exception instanceof AccessDeniedException) {
                $response = ResponseFactory::createErrorResponse(
                    $event->getRequest(),
                    $exception->getMessage(),
                    (int) $exception->getCode(),
                    [],
                    Response::HTTP_FORBIDDEN
                );
            } elseif ($exception instanceof HttpException) {
                $response = ResponseFactory::createErrorResponse(
                    $event->getRequest(),
                    $exception->getMessage(),
                    (int) $exception->getCode()
                );
            } else {
                throw $exception;
            }
        } else {
            throw $exception;
        }

        $event->setResponse($response);
    }
}
