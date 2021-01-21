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

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Christin Gruber <c.gruber@touchdesign.de>
 */
final class TwitterTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('twitter' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'twitter', $this->getSupportedSchemes());
        }

        $token = $this->getUser($dsn);
        $consumerKey = $dsn->getRequiredOption('consumer_key');
        $consumerSecret = $dsn->getRequiredOption('consumer_secret');
        $secret = $dsn->getRequiredOption('secret');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new TwitterTransport($token, $secret, $consumerKey, $consumerSecret, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['twitter'];
    }
}
