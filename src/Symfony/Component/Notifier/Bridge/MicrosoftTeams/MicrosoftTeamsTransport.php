<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\MicrosoftTeams;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christin Gruber <c.gruber@touchdesign.de>
 */
final class MicrosoftTeamsTransport extends AbstractTransport
{
    public function __construct(HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('microsoftteams://%s', $this->getEndpoint());
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage && (null === $message->getOptions() || $message->getOptions() instanceof MicrosoftTeamsOptions);
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        $options = $message->getOptions() ?? [];
        $options['text'] = $message->getSubject();

        // @todo build the full endpoint

        $webhook = sprintf('https://%s', $this->getEndpoint());

        $response = $this->client->request('POST', $webhook, [
            'json' => array_filter($options),
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new TransportException(sprintf('Unable to post the Microsoft Teams message: "%s".', $result['error']['message'] ?? $response->getContent(false)), $response, $result['error']['code'] ?? $response->getStatusCode());
        }

        $result = $response->toArray(false);

        // @todo debug and remove
        dump($result);

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($result['id']);

        return $sentMessage;
    }
}
