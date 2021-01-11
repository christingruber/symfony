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

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Christin Gruber <c.gruber@touchdesign.de>
 */
final class MicrosoftTeamsTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('microsoftteams' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'microsoftteams', $this->getSupportedSchemes());
        }

        // @todo implement

        return new MicrosoftTeamsTransport($this->client, $this->dispatcher);
    }

    protected function getSupportedSchemes(): array
    {
        return ['microsoftteams'];
    }
}
