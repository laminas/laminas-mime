<?php

namespace LaminasTest\Mime;

use Laminas\Mime;
use PHPUnit\Framework\TestCase;

use function base64_decode;
use function fclose;
use function file_get_contents;
use function fopen;
use function quoted_printable_decode;
use function realpath;
use function stream_get_contents;

/**
 * @group      Laminas_Mime
 */
class PartTest extends TestCase
{
    /**
     * MIME part test object
     *
     * @var Mime\Part
     */
    protected $part;

    /** @var string */
    protected $testText;

    protected function setUp(): void
    {
        $this->testText          = 'safdsafsa�lg ��gd�� sd�jg�sdjg�ld�gksd�gj�sdfg�dsj'
            . '�gjsd�gj�dfsjg�dsfj�djs�g kjhdkj fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg';
        $this->part              = new Mime\Part($this->testText);
        $this->part->encoding    = Mime\Mime::ENCODING_BASE64;
        $this->part->type        = "text/plain";
        $this->part->filename    = 'test.txt';
        $this->part->disposition = 'attachment';
        $this->part->charset     = 'iso8859-1';
        $this->part->id          = '4711';
    }

    public function testHeaders()
    {
        $expectedHeaders = [
            'Content-Type: text/plain',
            'Content-Transfer-Encoding: ' . Mime\Mime::ENCODING_BASE64,
            'Content-Disposition: attachment',
            'filename="test.txt"',
            'charset=iso8859-1',
            'Content-ID: <4711>',
        ];

        $actual = $this->part->getHeaders();

        foreach ($expectedHeaders as $expected) {
            $this->assertStringContainsString($expected, $actual);
        }
    }

    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $content              = $this->part->getContent();
        $this->assertEquals($this->testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Mime\Mime::ENCODING_8BIT;
        $content              = $this->part->getContent();
        $this->assertEquals($this->testText, $content);
    }

    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);

        // Test Base64
        $fp = fopen($testfile, 'rb');
        $this->assertIsResource($fp);
        $part           = new Mime\Part($fp);
        $part->encoding = Mime\Mime::ENCODING_BASE64;
        $fp2            = $part->getEncodedStream();
        $this->assertIsResource($fp2);
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded), $original);

        // test QuotedPrintable
        $fp = fopen($testfile, 'rb');
        $this->assertIsResource($fp);
        $part           = new Mime\Part($fp);
        $part->encoding = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2            = $part->getEncodedStream();
        $this->assertIsResource($fp2);
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded), $original);
    }

    /**
     * @group Laminas-1491
     */
    public function testGetRawContentFromPart()
    {
        $this->assertEquals($this->testText, $this->part->getRawContent());
    }

    /**
     * @link https://github.com/zendframework/zf2/issues/5428
     *
     * @group 5428
     */
    public function testContentEncodingWithStreamReadTwiceINaRow()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);

        $fp                       = fopen($testfile, 'rb');
        $part                     = new Mime\Part($fp);
        $part->encoding           = Mime\Mime::ENCODING_BASE64;
        $contentEncodedFirstTime  = $part->getContent();
        $contentEncodedSecondTime = $part->getContent();
        $this->assertEquals($contentEncodedFirstTime, $contentEncodedSecondTime);
        fclose($fp);

        $fp                       = fopen($testfile, 'rb');
        $part                     = new Mime\Part($fp);
        $part->encoding           = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $contentEncodedFirstTime  = $part->getContent();
        $contentEncodedSecondTime = $part->getContent();
        $this->assertEquals($contentEncodedFirstTime, $contentEncodedSecondTime);
        fclose($fp);
    }

    public function testSettersGetters()
    {
        $part = new Mime\Part();
        $part->setContent($this->testText)
             ->setEncoding(Mime\Mime::ENCODING_8BIT)
             ->setType('text/plain')
             ->setFilename('test.txt')
             ->setDisposition('attachment')
             ->setCharset('iso8859-1')
             ->setId('4711')
             ->setBoundary('frontier')
             ->setLocation('fiction1/fiction2')
             ->setLanguage('en')
             ->setIsStream(false)
             ->setFilters(['foo'])
             ->setDescription('foobar');

        $this->assertEquals($this->testText, $part->getContent());
        $this->assertEquals(Mime\Mime::ENCODING_8BIT, $part->getEncoding());
        $this->assertEquals('text/plain', $part->getType());
        $this->assertEquals('test.txt', $part->getFileName());
        $this->assertEquals('attachment', $part->getDisposition());
        $this->assertEquals('iso8859-1', $part->getCharset());
        $this->assertEquals('4711', $part->getId());
        $this->assertEquals('frontier', $part->getBoundary());
        $this->assertEquals('fiction1/fiction2', $part->getLocation());
        $this->assertEquals('en', $part->getLanguage());
        $this->assertEquals(false, $part->isStream());
        $this->assertEquals(['foo'], $part->getFilters());
        $this->assertEquals('foobar', $part->getDescription());
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public function invalidContentTypes(): array
    {
        return [
            'null'       => [null],
            'false'      => [false],
            'true'       => [true],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['string']],
            'object'     => [(object) ['content' => 'string']],
        ];
    }

    /**
     * @dataProvider invalidContentTypes
     * @param mixed $content
     */
    public function testConstructorRaisesInvalidArgumentExceptionForInvalidContentTypes($content)
    {
        $this->expectException(Mime\Exception\InvalidArgumentException::class);
        new Mime\Part($content);
    }

    /**
     * @dataProvider invalidContentTypes
     * @param mixed $content
     */
    public function testSetContentRaisesInvalidArgumentExceptionForInvalidContentTypes($content)
    {
        $part = new Mime\Part();
        $this->expectException(Mime\Exception\InvalidArgumentException::class);
        $part->setContent($content);
    }

    public function testBinaryPart()
    {
        $content      = file_get_contents(__DIR__ . '/TestAsset/laminas.png');
        $inputMessage = new Mime\Message();
        $inputMessage->addPart(new Mime\Part('Hello World'));
        $inputMessage->addPart(new Mime\Part($content));

        $outputMessage = Mime\Message::createFromMessage(
            $inputMessage->generateMessage(),
            $inputMessage->getMime()->boundary()
        );
        $parts         = $outputMessage->getParts();
        $this->assertCount(2, $parts);
        $this->assertEquals($content, $parts[1]->getContent());
    }
}
