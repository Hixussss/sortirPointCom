<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;

class SetLocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, string $defaultLocale = 'fr')
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        if (!$request->hasPreviousSession()) {
            return;
        }

        // Définir la langue à partir du cookie, de la session ou de la valeur par défaut
        $locale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
