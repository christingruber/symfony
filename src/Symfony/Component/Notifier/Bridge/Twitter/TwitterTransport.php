<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Twitter;

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
final class TwitterTransport extends AbstractTransport
{
    protected const HOST = 'api.twitter.com';

    private $token;
    private $secret;
    private $consumerKey;
    private $consumerSecret;

    public function __construct(string $token, string $secret, string $consumerKey, string $consumerSecret, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->token = $token;
        $this->secret = $secret;
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('twitter://%s?consumer_key=%s&consumer_secret=%s&secret=%s', $this->getEndpoint(), $this->consumerKey, $this->consumerKey, $this->secret);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage;
    }

    /**
     * @see https://developer.twitter.com/en/docs/api-reference-index#twitter-api-v1
     */
    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        $endpoint = sprintf('https://%s/1.1/direct_messages/events/new.json', $this->getEndpoint());

        $response = $this->client->request('POST', $endpoint, [
            'authorization' => $this->generateAuthorizationHeader(),
            'json' => [
                'event' => [
                    'type' => 'message_create',
                    'message_create' => [
                        'target' => [
                            'recipient_id' => $message->getRecipientId(),
                            'message_data' => $message->getSubject(),
                        ],
                    ],
                ],
            ],
        ]);

        $result = $response->toArray(false);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new TransportException(sprintf('Unable to post the Twitter message: "%s".', $result['error']), $response);
        }

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($result['id']);

        return $sentMessage;
    }

    private function generateAuthorizationHeader(): string
    {
        return sprintf(
            'OAuth oauth_consumer_key="%s", oauth_nonce="%s", oauth_signature="%s", oauth_signature_method="HMAC-SHA1", oauth_timestamp="%", oauth_token="%s", oauth_version="1.0"',
            $this->consumerKey,
            NONCE,
            SIGNATURE,
            time(),
            $this->token);
    }
}
