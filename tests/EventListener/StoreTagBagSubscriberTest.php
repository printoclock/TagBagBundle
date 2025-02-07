<?php

declare(strict_types=1);

namespace Setono\TagBagBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\TagBag\TagBagInterface;
use Setono\TagBagBundle\EventListener\StoreTagBagSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\SessionListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @covers \Setono\TagBagBundle\EventListener\StoreTagBagSubscriber
 */
final class StoreTagBagSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_stores(): void
    {
        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->store()->shouldBeCalled();

        $subscriber = new StoreTagBagSubscriber($tagBag->reveal());
        $subscriber->onKernelResponse($this->getResponseEvent(true));
    }

    /**
     * @test
     */
    public function it_does_not_store_when_the_request_is_not_a_master_request(): void
    {
        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->store()->shouldNotBeCalled();

        $subscriber = new StoreTagBagSubscriber($tagBag->reveal());
        $subscriber->onKernelResponse($this->getResponseEvent(false));
    }

    /**
     * @test
     */
    public function it_listens_to_the_correct_event(): void
    {
        $subscribedEvents = StoreTagBagSubscriber::getSubscribedEvents();
        $this->assertCount(1, $subscribedEvents);
        $this->assertTrue(isset($subscribedEvents[KernelEvents::RESPONSE]));
    }

    /**
     * @test
     */
    public function it_has_the_correct_priority(): void
    {
        $priority = StoreTagBagSubscriber::getSubscribedEvents()[KernelEvents::RESPONSE][1];

        /** @psalm-suppress InternalMethod */
        $sessionListenerPriority = SessionListener::getSubscribedEvents()[KernelEvents::RESPONSE][1];

        $this->assertGreaterThan($sessionListenerPriority, $priority);
    }

    private function getResponseEvent(bool $masterRequest): ResponseEvent
    {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            $masterRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
            new Response()
        );
    }
}
