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

use Symfony\Component\Notifier\Bridge\Twitter\TwitterTransportFactory;
use Symfony\Component\Notifier\Tests\TransportFactoryTestCase;
use Symfony\Component\Notifier\Transport\TransportFactoryInterface;

/**
 * @author Christin Gruber <c.gruber@touchdesign.de>
 */
final class TwitterTransportFactoryTest extends TransportFactoryTestCase
{
    public function createFactory(): TransportFactoryInterface
    {
        return new TwitterTransportFactory();
    }

    public function createProvider(): iterable
    {
        yield [
            'twitter://api.twitter.com?room_id=5539a3ee5etest0d3255bfef',
            'twitter://token@api.twitter.com?room_id=5539a3ee5etest0d3255bfef',
        ];
    }

    public function supportsProvider(): iterable
    {
        yield [true, 'twitter://token@host?room_id=5539a3ee5etest0d3255bfef'];
        yield [false, 'somethingElse://token@host?room_id=5539a3ee5etest0d3255bfef'];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield 'missing token' => ['twitter://api.twitter.com?room_id=5539a3ee5etest0d3255bfef'];
    }

    public function missingRequiredOptionProvider(): iterable
    {
        yield 'missing option: room_id' => ['twitter://token@host'];
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://token@host?room_id=5539a3ee5etest0d3255bfef'];
        yield ['somethingElse://token@host'];
    }
}
