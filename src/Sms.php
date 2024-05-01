<?php


namespace Libriciel\LsMessageWrapper;

use App\Service\SmsException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

class Sms implements JsonSerializable
{
    public const MAX_CHARACTERS_MESSAGE = 160;
    public const MIN_SENDER = 3;
    public const MAX_SENDER = 11;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $phone;

    /**
     * @var string
     */
    private string $content;

    /**
     * @var string
     */
    private string $sender;

    /**
     * Sms constructor.
     * @param string $name
     * @param string $phone
     * @param string $content
     * @param string $sender
     * @throws LsMessageException
     */
    public function __construct(string $name, string $phone, string $content, string $sender)
    {
        $this->checkName($name);
        $this->checkPhone($phone);
        $this->checkContent($content);
        $this->checkSender($sender);

        $this->name = $name;
        $this->phone = $phone;
        $this->content = $content;
        $this->sender = $sender;
    }

    private function checkName(?string $name): void
    {
        if (!$name) {
            throw new LsMessageException("message must be not empty", 400);
        }

        if (strlen($name) >= 255) {
            throw new LsMessageException("max name length must be not exceed 255 characters", 400);
        }
    }

    private function checkPhone(string $phone): void
    {
        if (!isset($phone) || !$phone || !preg_match("/^0(6|7)\d{8}$/", $phone)) {
            throw new LsMessageException("phone must be 06XXXXXXXX or 07XXXXXXXX", 400);
        }
    }

    private function checkContent(string $content): void
    {
        if (!isset($content) || !$content) {
            throw new LsMessageException("message must be not empty", 400);
        }

        if (strlen($content) >= SMS::MAX_CHARACTERS_MESSAGE) {
            throw new LsMessageException("message must be not exceed " . SMS::MAX_CHARACTERS_MESSAGE . " characters", 400);
        }
    }

    private function checkSender(string $sender): void
    {
        if (empty($sender)) {
            throw new LsMessageException("sender must be not empty", 400);
        }

        if (preg_match('/[^\w\s]/', $sender)) {
            throw new LsMessageException("sender cannot have special characters", 400);
        }

        if (strlen($sender) < SMS::MIN_SENDER) {
            throw new LsMessageException("sender must not be less than " . SMS::MIN_SENDER . " characters", 400);
        }

        if (strlen($sender) > SMS::MAX_SENDER) {
            throw new LsMessageException("sender must be not exceed " . SMS::MAX_SENDER . " characters", 400);
        }
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }
}
