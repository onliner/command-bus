<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\Serializer;

use Onliner\CommandBus\Remote\Serializer\NativeSerializer;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;

class NativeSerializerTest extends TestCase
{
    public function testSerializeUnserialize(): void
    {
        $serializer = new NativeSerializer();
        $command = new Command\Hello('test');

        self::assertEquals($command, $serializer->unserialize($serializer->serialize($command)));
    }
}
