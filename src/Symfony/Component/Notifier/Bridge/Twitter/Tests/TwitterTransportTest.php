<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Twitter\Tests;

use Symfony\Component\Notifier\Bridge\Twitter\TwitterTransport;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Tests\TransportTestCase;
use Symfony\Component\Notifier\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christin Gruber <c.gruber@touchdesign.de>
 */
final class TwitterTransportTest extends TransportTestCase
{
    public function createTransport(?HttpClientInterface $client = null): TransportInterface
    {
        return (new TwitterTransport('token', '5539a3ee5etest0d3255bfef', $client ?: $this->createMock(HttpClientInterface::class)))->setHost('api.gitter.im');
    }

    public function toStringProvider(): iterable
    {
        yield ['twitter://api.gitter.im?room_id=5539a3ee5etest0d3255bfef', $this->createTransport()];
    }

    public function supportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
    }

    public function unsupportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
        yield [$this->createMock(MessageInterface::class)];
    }
}
