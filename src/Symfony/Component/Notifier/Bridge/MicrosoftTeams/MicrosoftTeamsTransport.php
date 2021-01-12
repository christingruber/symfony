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

use Symfony\Component\HttpFoundation\Response;
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
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string Webhook connector path.
     */
    protected $path;

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

        $options = $message->getOptions();
        if ($options && !$message->getOptions() instanceof MicrosoftTeamsOptions) {
            throw new \LogicException(sprintf('The "%s" transport only supports instances of "%s" for options.', __CLASS__, MicrosoftTeamsOptions::class));
        }

        if (!$options && $notification = $message->getNotification()) {
            $options = $options = MicrosoftTeamsOptions::fromNotification($notification) ? $options->toArray() : [];
        } else {
            $options['text'] = $message->getSubject();
        }

        $this->response = $this->client->request('POST', $this->getEndpoint(), [
            'json' => array_filter($options),
        ]);

        if ($error = $this->hasError()) {
            throw new TransportException(sprintf('Unable to post the Microsoft Teams message, status code is "%d" expected was "%s". Error message is: %s.', $error['statusCode'], Response::HTTP_OK, $error['message']), $this->response);
        }

        return new SentMessage($message, (string) $this);
    }

    /**
     * @return $this
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    protected function getEndpoint(): ?string
    {
        return sprintf(
            'https://%s:%s%s',
            $this->host,
            $this->port ?? '443', // @todo replace
            $this->path ?? ''
        );
    }

    protected function hasError():?array
    {
        $statusCode = $this->response->getStatusCode();
        $content = $this->response->getContent(false) ?? 'Unknown error.';

        if (Response::HTTP_OK != $statusCode || 1 != $content) {
            return [
                'statusCode' => $statusCode,
                'message' => $content
            ];
        }

        return null;
    }

}
