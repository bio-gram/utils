<?php

namespace PrivateDev\Utils\Listener;

use PrivateDev\Utils\Error\Error;
use PrivateDev\Utils\Exception\ClientException;
use PrivateDev\Utils\Json\JsonResponseBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    /**
     * @var string
     */
    private $env;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonResponseBuilder
     */
    private $responseBuilder;

    /**
     * ExceptionListener constructor.
     *
     * @param string              $env
     * @param LoggerInterface     $logger
     * @param JsonResponseBuilder $responseBuilder
     */
    public function __construct(string $env, LoggerInterface $logger, JsonResponseBuilder $responseBuilder)
    {
        $this->env = $env;
        $this->logger = $logger;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            $response = $this->responseBuilder
                ->addError(new Error($exception->getMessage()))
                ->build($exception->getStatusCode());
        } elseif ($exception instanceof ClientException) {
            $response = $this->responseBuilder
                ->addError(new Error($exception->getMessage()))
                ->build(Response::HTTP_BAD_REQUEST);
        } else {
            $message = $this->env == "prod"
                ? "Unknown error"
                : $exception->getMessage() . " " . $exception->getTraceAsString();

            $response = $this->responseBuilder
                ->addError(new Error($message))
                ->build(Response::HTTP_INTERNAL_SERVER_ERROR);

            $this->logException($exception, sprintf('Uncaught PHP Exception %s: "%s" at %s line %s',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));
        }

        $event->setResponse($response);
    }

    /**
     * Logs an exception.
     *
     * @param \Exception $exception The \Exception instance
     * @param string     $message   The error message to log
     */
    protected function logException(\Exception $exception, $message)
    {
        if (null == $this->logger) {
            return;
        }

        if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
            $this->logger->critical($message, ['exception' => $exception]);
        } else {
            $this->logger->error($message, ['exception' => $exception]);
        }
    }
}