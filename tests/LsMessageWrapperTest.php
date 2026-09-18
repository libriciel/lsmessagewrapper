<?php

declare(strict_types=1);

namespace Libriciel\LsMessageWrapper\Test;

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

final class LsMessageWrapperTest extends TestCase
{
    private function mockResponse(int $statusCode, ?string $reasonPhrase = '', ?StreamInterface $body = null): ResponseInterface
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getReasonPhrase')->willReturn($reasonPhrase);
        $response->method('getBody')->willReturn($body);

        return $response;
    }

    private function mockBody(string $json): StreamInterface
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn($json);

        return $stream;
    }

    private function mockRequestFactory(): RequestFactoryInterface
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $request->method('getBody')->willReturn($this->createStub(StreamInterface::class));

        $factory = $this->createStub(RequestFactoryInterface::class);
        $factory->method('createRequest')->willReturn($request);

        return $factory;
    }

    private function mockClient(
        ?ResponseInterface $response,
        ?ClientExceptionInterface $exception = null,
    ): ClientInterface {
        $client = $this->createStub(ClientInterface::class);
        if (!$exception) {
            $client->method('sendRequest')->willReturn($response);
        } else {
            $client->method('sendRequest')->willThrowException($exception);
        }

        return $client;
    }

    public function testSetUrl(): void
    {
        $this->expectNotToPerformAssertions();

        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://testurl.fr');
    }

    public function testSetUrlRemovesTrailingSlash(): void
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method('withHeader')->willReturnSelf();

        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://test.libriciel.fr/api/v1/info')
            ->willReturn($request);

        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $msgWrapper = new LsMessageWrapper($this->mockClient($response), $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr/');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $msgWrapper->info();
    }

    public function testSetUrlMalformed(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('malformed url : https required');
        $this->expectExceptionCode(400);
        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('testurl.fr');
    }

    public function testSetUrlHttpRefused(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('malformed url : https required');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('http://testurl.fr');
    }

    public function testSetApiKey(): void
    {
        $this->expectNotToPerformAssertions();

        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey('myapiKey');
    }

    public function testSetApiKeyMalformed(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('malformed apiKey : no whitespace allowed');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey('my apiKey');
    }

    public function testSetUrlWithTrailingNewline(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('malformed url : https required');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl("https://testurl.fr\n");
    }

    public function testSetApiKeyWithTrailingNewline(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('malformed apiKey : no whitespace allowed');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey("myapiKey\n");
    }

    public function testInfoMissingUrl(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('url must be set');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey('myapiKey');

        $msgWrapper->info();
    }

    public function testInfoMissingApiKey(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('apiKey must be set');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');

        $msgWrapper->info();
    }

    public function testInfoMissingUrlAndApiKey(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $factory = $this->createStub(RequestFactoryInterface::class);

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('url and apiKey must be set');
        $this->expectExceptionCode(400);

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->info();
    }

    public function testInfoWrongApiKey(): void
    {
        $factory = $this->mockRequestFactory();

        $response = $this->mockResponse(401, 'bad apKey', $this->mockBody('bad apKey'));
        $client = $this->mockClient($response);

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('bad apKey');
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
        $this->assertSame(['success' => true], $content);
    }

    public function testInfoClientException(): void
    {
        $clientException = $this->createStub(ClientExceptionInterface::class);
        $client = $this->mockClient(null, $clientException);

        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);

        $msgWrapper->info();
    }

    public function testInfoInvalidJsonResponse(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('not json'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('invalid response : Syntax error');
        $this->expectExceptionCode(500);

        $msgWrapper->info();
    }

    public function testInfoNonArrayJsonResponse(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('"ok"'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('invalid response : JSON object expected');
        $this->expectExceptionCode(500);

        $msgWrapper->info();
    }

    public function testSendOneInvalidUtf8Content(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = new Sms('name', '0602030405', "contenu \xB1 invalide", 'sender');

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqzszcdcdcdc');

        $this->expectException(LsMessageException::class);
        $this->expectExceptionMessage('unable to encode request body : Malformed UTF-8 characters, possibly incorrectly encoded');
        $this->expectExceptionCode(400);

        $msgWrapper->sendOne($sms);
    }

    public function testSendOne(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = new Sms('name', '0602030405', 'content', 'sender');

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->setApiKey('123');
        $msgWrapper->setUrl('https://123.com');

        $content = $msgWrapper->sendOne($sms);
        $this->assertSame(['success' => true], $content);
    }

    public function testSendOneNotEnoughCredit(): void
    {
        $response = $this->mockResponse(402, 'not enough credit', $this->mockBody('not enough credit'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = new Sms('name', '0602030405', 'content', 'sender');
        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setApiKey('123');
        $msgWrapper->setUrl('https://123.com');

        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(402);
        $this->expectExceptionMessage('not enough credit');
        $msgWrapper->sendOne($sms);
    }

    public function testSendOneClientException(): void
    {
        $clientException = $this->createStub(ClientExceptionInterface::class);
        $client = $this->mockClient(null, $clientException);

        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);
        $msgWrapper->setUrl('https://test.libriciel.fr');
        $msgWrapper->setApiKey('aqaqcdcdcdc');

        $sms = new Sms('name', '0602030405', 'content', 'sender');

        $this->expectException(LsMessageException::class);

        $msgWrapper->sendOne($sms);
    }

    public function testSendMultiple(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $sms = new Sms('name', '0602030405', 'content', 'sender');

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->setApiKey('123');
        $msgWrapper->setUrl('https://123.com');

        $content = $msgWrapper->sendMultiple([$sms]);
        $this->assertSame(['success' => true], $content);
    }

    public function testSendMultipleEmptyArray(): void
    {
        $response = $this->mockResponse(200, '', $this->mockBody('{"success":true}'));
        $client = $this->mockClient($response);
        $factory = $this->mockRequestFactory();

        $msgWrapper = new LsMessageWrapper($client, $factory);

        $msgWrapper->setApiKey('123');
        $msgWrapper->setUrl('https://123.com');

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
