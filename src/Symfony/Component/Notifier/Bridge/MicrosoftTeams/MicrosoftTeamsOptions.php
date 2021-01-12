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

use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\Notification\Notification;

/**
 * @author Christin Gruber <c.gruber@touchdesign.de>
 */
final class MicrosoftTeamsOptions implements MessageOptionsInterface
{
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public static function fromNotification(Notification $notification): self
    {
        $options = new self(
            ['text' => $notification->getSubject()]
        );

        return $options;
    }

    public function toArray(): array
    {
        return array_merge($this->options, [
            '@context' => 'http://schema.org/extensions',
            '@type' => 'MessageCard',
        ]);
    }

    public function title(string $title): self
    {
        $this->options['title'] = $title;

        return $this;
    }

    public function themeColor(string $themeColor): self
    {
        $this->options['themeColor'] = $themeColor;

        return $this;
    }
}
