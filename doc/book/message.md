# Laminas\\Mime\\Message

`Laminas\Mime\Message` represents a MIME compliant message that can contain one or
more separate Parts (represented as [Laminas\Mime\Part](part.md) instances).
Encoding and boundary handling are handled transparently by the class.
`Message` instances can also be generated from MIME strings.

## Instantiation

There is no explicit constructor for `Laminas\Mime\Message`.

## Adding MIME Parts

[Laminas\Mime\Part](part.md) instances can be added to a given `Message` instance by
calling `->addPart($part)`

An array with all [Part](part.md) instances in the `Message` is returned from
the method `getParts()`. The `Part` instances can then be modified on
retrieveal, as they are stored in the array as references. If parts are added
to the array or the sequence is changed, the array needs to be passed back to
the `Message` instance by calling `setParts($partsArray)`.

The function `isMultiPart()` will return `TRUE` if more than one part is
registered with the `Message` instance; when true, the instance will generate a
multipart MIME message.

## Boundary handling

`Laminas\Mime\Message` usually creates and uses its own `Laminas\Mime\Mime` instance
to generate a boundary.  If you need to define the boundary or want to change
the behaviour of the `Mime` instance used by `Message`, you can create the
`Mime` instance yourself and register it with your `Message` using the
`setMime()` method; this is an atypical occurrence.

`getMime()` returns the `Mime` instance to use when rendering the message via
`generateMessage()`.

`generateMessage()` renders the `Message` content to a string.

## Parsing a string to create a Laminas\\Mime\\Message object

`Laminas\Mime\Message` defines a static factory for parsing MIME-compliant message
strings and returning a `Laminas\Mime\Message` instance:

```php
$message = Laminas\Mime\Message::createFromMessage($string, $boundary);
```

As of version 2.6.1, You may also parse a single-part message by omitting the
`$boundary` argument:

```php
$message = Laminas\Mime\Message::createFromMessage($string);
```

## Available methods

`Laminas\Mime\Message` contains the following methods:

- `getParts`: Get the all `Laminas\Mime\Part`s in the message.
- `setParts($parts)`: Set the array of `Laminas\Mime\Part`s for the message.
- `addPart(Laminas\Mime\Part $part)`: Append a new `Laminas\Mime\Part` to the
  message.
- `isMultiPart`: Check if the message needs to be sent as a multipart MIME
  message.
- `setMime(Laminas\Mime\Mime $mime)`: Set a custom `Laminas\Mime\Mime` object for the
  message.
- `getMime`: Get the `Laminas\Mime\Mime` object for the message.
- `generateMessage($EOL = Laminas\Mime\Mime::LINEEND)`: Generate a MIME-compliant
  message from the current configuration.
- `getPartHeadersArray($partnum)`: Get the headers of a given part as an array.
- `getPartHeaders($partnum, $EOL = Laminas\Mime\Mime::LINEEND)`: Get the headers
  of a given part as a string.
- `getPartContent($partnum, $EOL = Laminas\Mime\Mime::LINEEND)`: Get the encoded
  content of a given part as a string.
