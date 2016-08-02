<?php

namespace PrivateDev\Utils\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $env;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $env, LoggerInterface $logger)
    {
        $this->env = $env;
        $this->logger = $logger;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->env === "dev") {
            return;
        }

        $exception = $event->getException();

        $this->logException($exception, sprintf('Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));

        $response = new JsonResponse();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setData(['error' => $exception->getMessage()]);
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            $response->setData(['error' =>  $this->env == "prod" ? "unknown error" : $exception->getMessage() . " " . $exception->getTraceAsString()]);
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