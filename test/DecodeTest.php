<?php

/**
 * @see       https://github.com/laminas/laminas-mime for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mime/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mime/blob/master/LICENSE.md New BSD License
 */

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
