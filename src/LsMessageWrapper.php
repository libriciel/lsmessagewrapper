<?php

namespace Libriciel\LsMessageWrapper;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class LsMessageWrapper
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private $url;
    private $apiKey;

    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @throws LsMessageException
     */
    public function setUrl(string $url): void
    {
        $httpRegex = "~^((https?)://).+$~";
        if (!preg_match($httpRegex, $url)) {
            throw new LsMessageException("malformed url : missing http|https", 400);
        }
        $this->url = $url;
    }

    /**
     * @throws LsMessageException
     */
    public function setApiKey(string $apiKey): void
    {
        $noWhitespace = "~^^\S+$~";

        if (!preg_match($noWhitespace, $apiKey)) {
            throw new LsMessageException("malformed apiKey : no whitespace allowed", 400);
        }
        $this->apiKey = $apiKey;
    }

    /**
     * @throws LsMessageException
     */
    private function isInit(): void
    {
        if (!$this->url && !$this->apiKey) {
            throw new LsMessageException("url and apiKey must be set", 400);
        }

        if (!$this->url) {
            throw new LsMessageException("url must be set", 400);
        }

        if (!$this->apiKey) {
            throw new LsMessageException("apiKey must be set", 400);
        }
    }

    /**
     * @throws LsMessageException
     */
    public function info(): array
    {
        $this->isInit();
        $request = $this->requestFactory->createRequest(
            "GET",
            "$this->url" . '/api/v1/info'
        )->withHeader('Authorization', 'Bearer ' . $this->apiKey);

        try {
            $response = $this->client->sendRequest($request);

            if ($response->getStatusCode() != 200) {
                throw new LsMessageException($response->getBody()->getContents(), $response->getStatusCode());
            }
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientExceptionInterface $e) {
            throw new LsMessageException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws LsMessageException
     */
    public function sendOne(Sms $sms): array
    {
        $this->isInit();
        return $this->sendPostRequest('api/v1/send', json_encode($sms));
    }

    /**
     * Send multiple message from the same sender
     * @throws LsMessageException
     */
    public function sendMultiple(array $smsArray): array
    {
        $this->isInit();
        if (!count($smsArray)) {
            throw new LsMessageException("empty sms array", 400);
        }
        $data = [
            'name' => $smsArray[0]->getName(),
            'sender' => $smsArray[0]->getSender(),
            'messages' => []

        ];
        foreach ($smsArray as $sms) {
            $data['messages'][] = ['content' => $sms->getContent(), 'phone' => $sms->getPhone()];
        }

        return $this->sendPostRequest('api/v1/send-bulk', json_encode($data));
    }

    /**
     * @return mixed
     * @throws LsMessageException
     */
    private function sendPostRequest(string $path, string $json)
    {
        $request = $this->requestFactory->createRequest(
            "POST",
            $this->url . '/' . $path
        )->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json');

        $request->getBody()->write($json);

        try {
            $response = $this->client->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                throw new LsMessageException($response->getBody()->getContents(), $response->getStatusCode());
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientExceptionInterface $e) {
            throw new LsMessageException($e->getMessage(), $e->getCode());
        }
    }

    public static function countGsmCharacters(string $text): int
    {
        $gsmBasic = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ`abcdefghijklmnopqrstuvwxyzäöñüà';
        $gsmExtended = '^{}\\[~]|€';

        $replacement = [
            '’' => "'",  // apostrophe typographique → apostrophe GSM
            '“' => '"',
            '”' => '"',
            '–' => '-', // tiret demi-cadratin
            '—' => '-', // tiret cadratin
        ];

        $count = 0;
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');

            if (strpos($gsmBasic, $char) !== false) {
                $count += 1;
            } elseif (strpos($gsmExtended, $char) !== false) {
                $count += 2;
            } else {
                $char = $replacement[$char] ?? '';

                if ($char === '') {
                    continue;
                }

                // Réévaluer le caractère nettoyé
                if (strpos($gsmBasic, $char) !== false) {
                    $count += 1;
                } elseif (strpos($gsmExtended, $char) !== false) {
                    $count += 2;
                }
            }
        }

        return $count;
    }
}
