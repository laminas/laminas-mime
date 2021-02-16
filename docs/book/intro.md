# Introduction

`Laminas\Mime\Mime` is a support class for handling multipart
[MIME](https://en.wikipedia.org/wiki/MIME) messages;
[laminas-mail](https://github.com/laminas/laminas-mail) relies on it for both
parsing and creating multipart messages. [`Laminas\Mime\Message`](message.md) can
also be consumed by applications requiring general MIME support.

## Static Methods and Constants

`Laminas\Mime\Mime` provides a set of static helper methods to work with MIME:

- `Laminas\Mime\Mime::isPrintable()`: Returns `TRUE` if the given string contains
  no unprintable characters, `FALSE` otherwise.
- `Laminas\Mime\Mime::encode()`: Encodes a string with the specified encoding.
- `Laminas\Mime\Mime::encodeBase64()`: Encodes a string into base64 encoding.
- `Laminas\Mime\Mime::encodeQuotedPrintable()`: Encodes a string with the
  quoted-printable mechanism.
- `Laminas\Mime\Mime::encodeBase64Header()`: Encodes a string into base64 encoding
  for Mail Headers.
- `Laminas\Mime\Mime::encodeQuotedPrintableHeader()`: Encodes a string with the
  quoted-printable mechanism for Mail Headers.
- `Laminas\Mime\Mime::mimeDetectCharset()`: detects if a string is encoded as
  ASCII, Base64, or quoted-printable.

`Laminas\Mime\Mime` defines a set of constants commonly used with MIME messages:

- `Laminas\Mime\Mime::TYPE_ENRICHED`: 'text/enriched'
- `Laminas\Mime\Mime::TYPE_HTML`: 'text/html'
- `Laminas\Mime\Mime::TYPE_OCTETSTREAM`: 'application/octet-stream'
- `Laminas\Mime\Mime::TYPE_TEXT`: 'text/plain'
- `Laminas\Mime\Mime::TYPE_XML`: 'text/xml'
- `Laminas\Mime\Mime::ENCODING_BASE64`: 'base64'
- `Laminas\Mime\Mime::ENCODING_7BIT`: '7bit'
- `Laminas\Mime\Mime::ENCODING_8BIT`: '8bit'
- `Laminas\Mime\Mime::ENCODING_QUOTEDPRINTABLE`: 'quoted-printable'
- `Laminas\Mime\Mime::DISPOSITION_ATTACHMENT`: 'attachment'
- `Laminas\Mime\Mime::DISPOSITION_INLINE`: 'inline'
- `Laminas\Mime\Mime::MESSAGE_DELIVERY_STATUS`: 'message/delivery-status'
- `Laminas\Mime\Mime::MULTIPART_ALTERNATIVE`: 'multipart/alternative'
- `Laminas\Mime\Mime::MULTIPART_MIXED`: 'multipart/mixed'
- `Laminas\Mime\Mime::MULTIPART_RELATED`: 'multipart/related'
- `Laminas\Mime\Mime::MULTIPART_RELATIVE`: 'multipart/relative'
- `Laminas\Mime\Mime::MULTIPART_REPORT`: 'multipart/report'
- `Laminas\Mime\Mime::MULTIPART_RFC822`: 'multipart/rfc822'

## Instantiating Laminas\\Mime

When instantiating a `Laminas\Mime\Mime` object, a MIME boundary is stored that is
used for all instance calls. If the constructor is called with a string
parameter, this value is used as the MIME boundary; if not, a random MIME
boundary is generated.

A `Laminas\Mime\Mime` object has the following methods:

- `boundary()`: Returns the MIME boundary string.
- `boundaryLine()`: Returns the complete MIME boundary line.
- `mimeEnd()`: Returns the complete MIME end boundary line.
