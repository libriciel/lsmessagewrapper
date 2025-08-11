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
    public const REGEX_VALIDATION_SENDER = '/^[a-zA-Z0-9]+$/';

    private string $name;
    private string $phone;
    private string $content;
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
            throw new LsMessageException("name must be not empty", 400);
        }

        if (strlen($name) >= 255) {
            throw new LsMessageException("max name length must be not exceed 255 characters", 400);
        }
    }

    private function checkPhone(?string $phone): void
    {
        if (!isset($phone) || !$phone) {
            throw new LsMessageException("phone must be not empty", 400);
        }

        if (!preg_match("/^0(6|7)\d{8}$/", $phone)) {
            throw new LsMessageException("phone must be 06XXXXXXXX or 07XXXXXXXX", 400);
        }
    }

    private function checkContent(?string $content): void
    {
        if (!isset($content) || !$content) {
            throw new LsMessageException("message must be not empty", 400);
        }

        $nbContent = $this->countGsmCharacters($content);

        if ($nbContent >= SMS::MAX_CHARACTERS_MESSAGE) {
            throw new LsMessageException("message must be not exceed " . SMS::MAX_CHARACTERS_MESSAGE . " characters", 400);
        }
    }

    private function checkSender(?string $sender): void
    {
        if (empty($sender)) {
            throw new LsMessageException("sender must be not empty", 400);
        }

        if (!preg_match(SMS::REGEX_VALIDATION_SENDER, $sender)) {
            throw new LsMessageException("sender cannot have special characters or space. Be composed only of alphabetic (letters) or alphanumeric (letters and numbers) characters.", 400);
        }

        $nbSender = strlen($sender);
        if ($nbSender < SMS::MIN_SENDER) {
            throw new LsMessageException("sender must not be less than " . SMS::MIN_SENDER . " characters", 400);
        }

        if ($nbSender > SMS::MAX_SENDER) {
            throw new LsMessageException("sender must be not exceed " . SMS::MAX_SENDER . " characters", 400);
        }
    }

    public function countGsmCharacters(string $text): int
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

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSender(): string
    {
        return $this->sender;
    }
}
