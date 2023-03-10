<?php

declare(strict_types=1);

namespace GeekCell\DddBundle\EventSubscriber;

use GeekCell\DddBundle\Support\Facades\Facade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;

class FacadeSetupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Kernel $kernel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0],
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            Facade::setFacadeKernel($this->kernel);
        }
    }
}
