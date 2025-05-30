<?php

namespace App\EventSubscriber;

use App\Controller\LoginController;
use App\Controller\PublicController;
use App\Service\AuthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function __construct(private AuthService $authService)
    {
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if (!$controller instanceof LoginController && !$controller instanceof PublicController && !$this->authService->isLoggedIn()) {
            $this->authService->setLastUri($event->getRequest());
            $event->setController(fn () => new RedirectResponse('/login'));

            return;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            // RequestEvent::class => 'onKernelRequest',
        ];
    }
}
