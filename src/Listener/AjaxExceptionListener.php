<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 4:18 PM
 */

namespace App\Listener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AjaxExceptionListener {
    private $isDev;

    public function __construct(ParameterBagInterface $parameterBag){
        $this->isDev = $parameterBag->get("kernel.environment") === "dev";
    }

    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
        $request = $event->getRequest();
        if ($request->getContentType() == "json") {
            $code = 400;
            $rtn = [
                "status" => "failure",
                "message" => $exception->getMessage()
            ];
            switch (get_class($exception)) {
                case NotFoundHttpException::class:
                    $code = 404;
                    break;
                case ValidationException::class:
                    /* @var \App\Exception\ValidationException $exception */
                    foreach ($exception->getErrors() as $k => $v) {
                        $rtn["errors"][$k] = $v;
                    }
                    break;
            }
            if ($this->isDev) {
                $rtn["stackTrace"] = explode("\n", $exception->getTraceAsString());
            }
            $event->setResponse(new JsonResponse($rtn, $code));
        }
    }
}