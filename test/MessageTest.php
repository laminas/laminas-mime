<?php

declare(strict_types=1);

namespace LaminasTest\Mime;

use Laminas\Mail\Headers;
use Laminas\Mime;
use Laminas\Mime\AlternativePart;
use Laminas\Mime\Message;
use Laminas\Mime\Part;
use PHPUnit\Framework\TestCase;

use function count;
use function current;
use function strlen;
use function strpos;

/**
 * @group      Laminas_Mime
 */
class MessageTest extends TestCase
{
    public function testMultiPart()
    {
        $msg = new Mime\Message();  // No Parts
        $this->assertFalse($msg->isMultiPart());
    }

    public function testSetGetParts()
    {
        $msg = new Mime\Message();  // No Parts
        $p   = $msg->getParts();
        $this->assertIsArray($p);
        $this->assertEmpty($p);

        $p2   = [];
        $p2[] = new Mime\Part('This is a test');
        $p2[] = new Mime\Part('This is another test');
        $msg->setParts($p2);
        $p = $msg->getParts();
        $this->assertIsArray($p);
        $this->assertCount(2, $p);
    }

    public function testGetMime()
    {
        $msg = new Mime\Message();  // No Parts
        $m   = $msg->getMime();
        $this->assertInstanceOf(\Laminas\Mime\Mime::class, $m);

        $msg  = new Mime\Message();  // No Parts
        $mime = new Mime\Mime('1234');
        $msg->setMime($mime);
        $m2 = $msg->getMime();
        $this->assertInstanceOf(\Laminas\Mime\Mime::class, $m2);
        $this->assertEquals('1234', $m2->boundary());
    }

    public function testGenerate()
    {
        $msg = new Mime\Message();  // No Parts
        $p1  = new Mime\Part('This is a test');
        $p2  = new Mime\Part('This is another test');
        $msg->addPart($p1);
        $msg->addPart($p2);
        $res      = $msg->generateMessage();
        $mime     = $msg->getMime();
        $boundary = $mime->boundary();
        $p1       = strpos($res, $boundary);
        // $boundary must appear once for every mime part
        $this->assertNotFalse($p1);
        if ($p1) {
            $p2 = strpos($res, $boundary, $p1 + strlen($boundary));
            $this->assertNotFalse($p2);
        }
        // check if the two test messages appear:
        $this->assertStringContainsString('This is a test', $res);
        $this->assertStringContainsString('This is another test', $res);
        // ... more in ZMailTest
    }

    /**
     * check if decoding a string into a \Laminas\Mime\Message object works
     */
    public function testDecodeMimeMessage()
    {
        $text = <<<EOD
This is a message in Mime Format.  If you see this, your mail reader does not support this format.

--=_af4357ef34b786aae1491b0a2d14399f
Content-Type: application/octet-stream
Content-Transfer-Encoding: 8bit

This is a test
--=_af4357ef34b786aae1491b0a2d14399f
Content-Type: image/gif
Content-Transfer-Encoding: base64
Content-ID: <12>

This is another test
--=_af4357ef34b786aae1491b0a2d14399f--
EOD;
        $res  = Mime\Message::createFromMessage($text, '=_af4357ef34b786aae1491b0a2d14399f');

        $parts = $res->getParts();
        $this->assertEquals(2, count($parts));

        $part1 = $parts[0];
        $this->assertEquals('application/octet-stream', $part1->type);
        $this->assertEquals('8bit', $part1->encoding);

        $part2 = $parts[1];
        $this->assertEquals('image/gif', $part2->type);
        $this->assertEquals('base64', $part2->encoding);
        $this->assertEquals('12', $part2->id);
    }

    /**
     * check if decoding a string into a \Laminas\Mime\Message object works
     */
    public function testDecodeMimeMessageNoHeader()
    {
        $text = <<<EOD
This is a MIME-encapsulated message

--=_af4357ef34b786aae1491b0a2d14399f

The original message was received at Fri, 16 Aug 2013 00:00:48 -0700
from localhost.localdomain [127.0.0.1]
End content

--=_af4357ef34b786aae1491b0a2d14399f
Content-Type: image/gif

This is a test
--=_af4357ef34b786aae1491b0a2d14399f--
EOD;
        $res  = Mime\Message::createFromMessage($text, '=_af4357ef34b786aae1491b0a2d14399f');

        $parts = $res->getParts();
        $this->assertEquals(2, count($parts));

        $part1        = $parts[0];
        $part1Content = $part1->getRawContent();
        $this->assertStringContainsString('The original message', $part1Content);
        $this->assertStringContainsString('End content', $part1Content);

        $part2 = $parts[1];
        $this->assertEquals('image/gif', $part2->type);
    }

    /**
     * Check if decoding a string that is not a multipart message works
     */
    public function testDecodeNonMultipartMimeMessage()
    {
        $text = <<<EOD
Content-Type: image/gif

This is a test
EOD;
        $res  = Mime\Message::createFromMessage($text);

        $parts = $res->getParts();
        $this->assertEquals(1, count($parts));

        $part1        = $parts[0];
        $part1Content = $part1->getRawContent();
        $this->assertEquals('This is a test', $part1Content);
        $this->assertEquals('image/gif', $part1->type);
    }

    public function testNonMultipartMessageShouldNotRemovePartFromMessage()
    {
        $message = new Mime\Message();  // No Parts
        $part    = new Mime\Part('This is a test');
        $message->addPart($part);
        $message->generateMessage();

        $parts = $message->getParts();
        $test  = current($parts);
        $this->assertSame($part, $test);
    }

    /**
     * @group Laminas-5962
     */
    public function testPassEmptyArrayIntoSetPartsShouldReturnEmptyString()
    {
        $mimeMessage = new Mime\Message();
        $mimeMessage->setParts([]);

        $this->assertEquals('', $mimeMessage->generateMessage());
    }

    public function testDuplicatePartAddedWillThrowException()
    {
        $this->expectException(Mime\Exception\InvalidArgumentException::class);

        $message = new Mime\Message();
        $part    = new Mime\Part('This is a test');
        $message->addPart($part);
        $message->addPart($part);
    }

    public function testFromStringWithCrlfAndRfc2822FoldedHeaders()
    {
        // This is a fixture as provided by many mailservers
        // e.g. cyrus or dovecot
        $eol     = "\r\n";
        $fixture = 'This is a MIME-encapsulated message' . $eol . $eol
            . '--=_af4357ef34b786aae1491b0a2d14399f' . $eol
            . 'Content-Type: text/plain' . $eol
            . 'Content-Disposition: attachment;' . $eol
            . "\t" . 'filename="test.txt"' . $eol // Valid folding
            . $eol
            . 'This is a test' . $eol
            . '--=_af4357ef34b786aae1491b0a2d14399f--';

        $message = Message::createFromMessage($fixture, '=_af4357ef34b786aae1491b0a2d14399f', $eol);
        $parts   = $message->getParts();

        $this->assertEquals(1, count($parts));
        $this->assertEquals('attachment; filename="test.txt"', $parts[0]->getDisposition());
    }

    public function testDecodeMultipartMimeMessageWithMessagePartAlternatives()
    {
        $rawMessage = <<<EOL
--001a11447dc881e40f0537fe6d5a
Content-Type: multipart/alternative; boundary=001a11447dc881e40b0537fe6d58

--001a11447dc881e40b0537fe6d58
Content-Type: text/plain; charset=UTF-8

This is a test email with 1 attachment.

--001a11447dc881e40b0537fe6d58
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

<div dir=3D"ltr">This is a test email with 1 attachment.<br clear=3D"all"><=
div><br></div>-- <br><div class=3D"gmail_signature" data-smartmail=3D"gmail=
_signature"><div dir=3D"ltr"><img src=3D"https://sendgrid.com/brand/sg-logo=
-email.png" width=3D"96" height=3D"17"><br><div><br></div></div></div>
</div>

--001a11447dc881e40b0537fe6d58--

--001a11447dc881e40f0537fe6d5a
Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document;
    name="DockMcWordface.docx"
Content-Disposition: attachment; filename="DockMcWordface.docx"
Content-Transfer-Encoding: base64
X-Attachment-Id: f_iqtleujy0

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

--001a11447dc881e40f0537fe6d5a--
EOL;
        $boundary = '001a11447dc881e40f0537fe6d5a';

        $message = Message::createFromMessage($rawMessage, $boundary);
        $parts = $message->getParts();

        $this->assertCount(2, $parts);

        $this->assertInstanceOf(Part::class, $parts[0]);
        $this->assertCount(2, $parts[0]->getParts());

        $plainTextPart = $parts[0]->getParts()[0];
        $this->assertSame("text/plain;\r\n charset=\"UTF-8\"", $plainTextPart->getType());
        $this->assertSame("This is a test email with 1 attachment.", trim($plainTextPart->getContent()));

        $htmlPart = $parts[0]->getParts()[1];
        $this->assertSame("text/html;\r\n charset=\"UTF-8\"", $htmlPart->getType());
        $partHeaders = trim(str_replace(["\r\n"], '', $htmlPart->getHeaders()));

        $headers = Headers::fromString(trim(str_replace(["\r\n"], '', $htmlPart->getHeaders())), "\n");
        $this->assertTrue($headers->has('Content-Transfer-Encoding'));
        $this->assertSame("quoted-printable", $headers->get('Content-Transfer-Encoding')->getFieldValue());

        $htmlBody = <<<EOF
<div dir="ltr">This is a test email with 1 attachment.<br clear="all"><div><br></div>-- <br><div class="gmail_signature" data-smartmail="gmail_signature"><div dir="ltr"><img src="https://sendgrid.com/brand/sg-logo-email.png" width="96" height="17"><br><div><br></div></div></div>
</div>
EOF;
        $this->assertSame($htmlBody, trim($htmlPart->getRawContent()));

        $this->assertInstanceOf(Part::class, $parts[1]);
        $headers = Headers::fromString(trim(str_replace(["\r\n"], '', $parts[1]->getHeaders())), "\n");
        $this->assertTrue($headers->has('Content-Disposition'));
        $this->assertSame(
            "attachment; filename=\"DockMcWordface.docx\"",
            $headers->get('Content-Disposition')->getFieldValue()
        );
        $this->assertSame(
            "base64",
            $headers->get('Content-Transfer-Encoding')->getFieldValue()
        );
        $this->assertSame(
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document;\r\n name=\"DockMcWordface.docx\"",
            $headers->get('Content-Type')->getFieldValue()
        );
    }
}
