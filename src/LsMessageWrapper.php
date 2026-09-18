<?php

declare(strict_types=1);

namespace Libriciel\LsMessageWrapper;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class LsMessageWrapper
{
    private ?string $url = null;
    private ?string $apiKey = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
    }

    /**
     * @throws LsMessageException
     */
    public function setUrl(string $url): void
    {
        $url = rtrim($url, '/');

        $httpRegex = '~^https://.+\z~';
        if (!preg_match($httpRegex, $url)) {
            throw new LsMessageException('malformed url : https required', 400);
        }
        $this->url = $url;
    }

    /**
     * @throws LsMessageException
     */
    public function setApiKey(string $apiKey): void
    {
        $noWhitespace = '~^\S+\z~';

        if (!preg_match($noWhitespace, $apiKey)) {
            throw new LsMessageException('malformed apiKey : no whitespace allowed', 400);
        }
        $this->apiKey = $apiKey;
    }

    /**
     * @throws LsMessageException
     */
    private function isInit(): void
    {
        if (!$this->url && !$this->apiKey) {
            throw new LsMessageException('url and apiKey must be set', 400);
        }

        if (!$this->url) {
            throw new LsMessageException('url must be set', 400);
        }

        if (!$this->apiKey) {
            throw new LsMessageException('apiKey must be set', 400);
        }
    }

    /**
     * @return array<mixed>
     * @throws LsMessageException
     */
    public function info(): array
    {
        return $this->sendRequest('GET', 'api/v1/info');
    }

    /**
     * @return array<mixed>
     * @throws LsMessageException
     */
    public function sendOne(Sms $sms): array
    {
        return $this->sendRequest('POST', 'api/v1/send', $sms);
    }

    /**
     * Send multiple message from the same sender
     * @param list<Sms> $smsArray
     * @return array<mixed>
     * @throws LsMessageException
     */
    public function sendMultiple(array $smsArray): array
    {
        $this->isInit();
        if (!count($smsArray)) {
            throw new LsMessageException('empty sms array', 400);
        }
        $data = [
            'name' => $smsArray[0]->getName(),
            'sender' => $smsArray[0]->getSender(),
            'messages' => [],
        ];
        foreach ($smsArray as $sms) {
            $data['messages'][] = ['content' => $sms->getContent(), 'phone' => $sms->getPhone()];
        }

        return $this->sendRequest('POST', 'api/v1/send-bulk', $data);
    }

    /**
     * @return array<mixed>
     * @throws LsMessageException
     */
    private function sendRequest(string $method, string $path, mixed $body = null): array
    {
        $this->isInit();
        $request = $this->requestFactory->createRequest($method, $this->url . '/' . $path)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey);

        if ($body !== null) {
            $request = $request->withHeader('Content-Type', 'application/json');
            try {
                $request->getBody()->write(json_encode($body, JSON_THROW_ON_ERROR));
            } catch (JsonException $e) {
                throw new LsMessageException('unable to encode request body : ' . $e->getMessage(), 400, $e);
            }
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new LsMessageException($e->getMessage(), $e->getCode(), $e);
        }

        $content = $response->getBody()->getContents();
        if ($response->getStatusCode() !== 200) {
            throw new LsMessageException($content, $response->getStatusCode());
        }

        try {
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new LsMessageException('invalid response : ' . $e->getMessage(), 500, $e);
        }
        if (!is_array($data)) {
            throw new LsMessageException('invalid response : JSON object expected', 500);
        }

        return $data;
    }

    public static function countGsmCharacters(string $text): int
    {
        // Normalisation des fins de ligne
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Tableau de remplacements
        $replacement = Sms::REMPLACEMENTS_CARACTERES;

        // Caractères qui comptent double
        $doubleChars = ['€', "\n"];

        // Remplacement des caractères
        $normalized = strtr($text, $replacement);

        $count = 0;
        $length = mb_strlen($normalized, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($normalized, $i, 1, 'UTF-8');
            $count += in_array($char, $doubleChars, true) ? 2 : 1;
        }

        return $count;
    }
}
