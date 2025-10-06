<?php

use Libriciel\LsMessageWrapper\LsMessageException;
use Libriciel\LsMessageWrapper\LsMessageWrapper;
use Libriciel\LsMessageWrapper\Sms;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class LsMessageWrapperTest extends TestCase
{
    private function mockResponse(int $statusCode, ?string $reasonPhrase = "", ?StreamInterface $body = null): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getReasonPhrase')->willReturn($reasonPhrase);
        $response->method('getBody')->willReturn($body);

        return $response;
    }

    private function mockBody(string $json): StreamInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($json);

        return $stream;
    }

    private function mockRequestFactory(): RequestFactoryInterface
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->will($this->returnSelf());

        $request->method('getBody')->willReturn($this->createMock(StreamInterface::class));

        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory->method('createRequest')->willReturn($request);

        return $factory;
    }

    private function mockClient(?ResponseInterface $response, Exception $exception = null): ClientInterface
    {
        $client = $this->createMock(ClientInterface::class);
        if (!$exception) {
            $client->method('sendRequest')->willReturn($response);
        } else {
            $client->method('sendRequest')->willThrowException($exception);
        }

        return $client;
    }

    public function testSetUrl(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl("http://testurl.fr");

        $this->assertTrue(true);
    }

    public function testSetUrlMalformed(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage("malformed url : missing http|https");
        $this->expectExceptionCode(400);
        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl("testurl.fr");

        $this->assertTrue(true);
    }

    public function testSetApiKey(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey("myapiKey");

        $this->assertTrue(true);
    }

    public function testSetApiKeyMalformed(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage("malformed apiKey : no whitespace allowed");
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey("my apiKey");

        $this->assertTrue(true);
    }

    public function testInfoMissingUrl(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage("url must be set");
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey('myapiKey');

        $msgWrapper->info();
    }

    public function testInfoMissingApiKey(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage("apiKey must be set");
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');

        $msgWrapper->info();
    }

    public function testInfoMissingUrlAndApiKey(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage("url and apiKey must be set");
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->info();
    }

    public function testInfoWrongApiKey(): void
    {
        $factory = $this->mockRequestFactory();

        $response = $this->mockResponse(401, 'bad apKey', $this->mockBody("bad apKey"));
        $client = $this->mockClient($response);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage("bad apKey");
        $this->expectExceptionCode(401);

        $msgWrapper->info();
    }

    public function testInfo(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $content = $msgWrapper->info();
        $this->assertSame(["success" => true], $content);
    }

    public function testInfoClientException(): void
    {
        $clientException = $this->createMock(ClientExceptionInterface::class);
        $client = $this->mockClient(null, $clientException);

        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);

        $msgWrapper->info();
    }

    public function testSendOne(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = $this->createMock(Sms::class);

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->setApiKey("123");
        $msgWrapper->setUrl("https://123.com");

        $content = $msgWrapper->sendOne($sms);
        $this->assertSame(["success" => true], $content);
    }

    public function testSendOneNotEnoughCredit(): void
    {
        $response = $this->mockResponse(402, 'not enough credit', $this->mockBody("not enough credit"));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = $this->createMock(Sms::class);
        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey("123");
        $msgWrapper->setUrl("https://123.com");

        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(402);
        $this->expectExceptionMessage('not enough credit');
        $msgWrapper->sendOne($sms);
    }

    public function testSendOneClientException(): void
    {
        $clientException = $this->createMock(ClientExceptionInterface::class);
        $client = $this->mockClient(null, $clientException);

        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqcdcdcdc');

        $sms = $this->createMock(Sms::class);

        $this->expectException(LsMessageException::class);

        $msgWrapper->sendOne($sms);
    }

    public function testSendMultiple(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = $this->createMock(Sms::class);

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->setApiKey("123");
        $msgWrapper->setUrl("https://123.com");

        $content = $msgWrapper->sendMultiple([$sms]);
        $this->assertSame(["success" => true], $content);
    }

    public function testSendMultipleEmptyArray(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->setApiKey("123");
        $msgWrapper->setUrl("https://123.com");

        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('empty sms array');

        $msgWrapper->sendMultiple([]);
    }

    public function testCountGsmCharactersSimpleText(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();
        $msgWrapper = new LsMessageWrapper($client, $factory);

        $text = 'Bonjour';
        $count = $msgWrapper->countGsmCharacters($text);

        $this->assertSame(7, $count);
    }

    public function testCountGsmCharactersWithAccents(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();
        $msgWrapper = new LsMessageWrapper($client, $factory);

        // Supposons que 'é' soit remplacé par 'e' dans REMPLACEMENTS_CARACTERES
        // Exemple : ['é' => 'e', 'è' => 'e']
        $text = 'Café';
        $count = $msgWrapper->countGsmCharacters($text);

        $this->assertSame(4, $count);
    }

    public function testCountGsmCharactersWithDoubleCharacters(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();
        $msgWrapper = new LsMessageWrapper($client, $factory);

        $text = "Salut €\n";
        // "Salut" = 5 caractères + " " = 1 + "€" = 2 + "\n" = 2

        $count = $msgWrapper->countGsmCharacters($text);

        $this->assertSame(10, $count);
    }

    public function testCountGsmCharactersEmptyString(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();
        $msgWrapper = new LsMessageWrapper($client, $factory);

        $count = $msgWrapper->countGsmCharacters('');

        $this->assertSame(0, $count);
    }

    public function testCountGsmCharactersWithReplacementTable(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();
        $msgWrapper = new LsMessageWrapper($client, $factory);

        $count = $msgWrapper->countGsmCharacters('ça va');

        // "ça va" devient "ca va" → 5 caractères
        $this->assertSame(5, $count);
    }
}