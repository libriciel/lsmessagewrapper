<?php


namespace Libriciel\LsMessageWrapper;

use JsonSerializable;

class Sms implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $sender;

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
        if (!$name || strlen($name) >= 255) {
            throw new LsMessageException("max name length must be < 255 and not empty", 400);
        }

        if (!$phone || !preg_match("/^0(6|7)\d{8}$/", $phone)) {
            throw new LsMessageException("phone must be 06XXXXXXXX or 07XXXXXXXX", 400);
        }

        if (!$content || strlen($content) >= 159) {
            throw new LsMessageException("message must by < 159 characters", 400);
        }

        if (!$sender || strlen($sender) > 11) {
            throw new LsMessageException("sender must by < 11 characters", 400);
        }

        $this->name = $name;
        $this->phone = $phone;
        $this->content = $content;
        $this->sender = $sender;
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
