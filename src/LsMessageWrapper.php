<?php


namespace Libriciel\LsMessageWrapper;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class LsMessageWrapper
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $apiKey;

    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param string $url
     * @throws LsMessageException
     */
    public function setUrl(string $url)
    {
        $httpRegex = "~^((https?)://).+$~";
        if (!preg_match($httpRegex, $url)) {
            throw new LsMessageException("malformed url : missing http|https", 400);
        }
        $this->url = $url;
    }

    /**
     * @param string $apiKey
     * @throws LsMessageException
     */
    public function setApiKey(string $apiKey)
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
    private function isInit()
    {
        if (!$this->url || !$this->apiKey) {
            throw new LsMessageException("url and apiKey must be set", 400);
        }
    }

    /**
     * @return array
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
                throw new LsMessageException($response->getReasonPhrase(), $response->getStatusCode());
            }
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientExceptionInterface $e) {
            throw new LsMessageException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Sms $sms
     * @return array
     * @throws LsMessageException
     */
    public function sendOne(Sms $sms): array
    {
        $this->isInit();
        return $this->sendPostRequest('api/v1/send', json_encode($sms));
    }

    /**
     * @param Sms[] $smsArray
     * @return array
     * @throws LsMessageException
     * Send multiple message from the same sender
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

        return $this->sendPostRequest('api/v1/sendBulk', json_encode($data));
    }

    /**
     * @param string $path
     * @param string $json
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
                throw new LsMessageException($response->getReasonPhrase(), $response->getStatusCode());
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientExceptionInterface $e) {
            throw new LsMessageException($e->getMessage(), $e->getCode());
        }
    }
}
