<?php

namespace App\EventSubscriber;

use App\Service\AuthService;
use App\Value\Language;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LanguageSubscriber implements EventSubscriberInterface
{
    public function __construct(private AuthService $authService)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $availableLanguages = array_map(
            fn (Language $lang) => $lang->value,
            Language::cases(),
        );

        $locale = $this->authService->getCurrentClown()?->getSettings()->language?->value;
        if (is_null($locale)) {
            $locale = $request->getPreferredLanguage($availableLanguages);
        }

        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 30],
        ];
    }
}
