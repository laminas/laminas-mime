<?php

declare(strict_types=1);

namespace LaminasTest\Mime;

use Laminas\Mail\Headers;
use Laminas\Mime\Decode;
use PHPUnit\Framework\TestCase;

class DecodeTest extends TestCase
{
    public function testDecodeMessageWithoutHeaders()
    {
        $text = 'This is a message body';

        Decode::splitMessage($text, $headers, $body);

        self::assertInstanceOf(Headers::class, $headers);
        self::assertSame($text, $body);
    }
}
