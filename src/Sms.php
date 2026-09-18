<?php

declare(strict_types=1);

namespace Libriciel\LsMessageWrapper;

use JsonSerializable;
use Override;

final class Sms implements JsonSerializable
{
    public const int MAX_CHARACTERS_MESSAGE = 160;
    public const int MIN_SENDER = 3;
    public const int MAX_SENDER = 11;
    public const string REGEX_VALIDATION_SENDER = '/^[a-zA-Z0-9]+$/';
    public const array REMPLACEMENTS_CARACTERES = [
        'ÿ' => 'y',
        'ç' => 'c',
        'Ç' => 'C',
        'û' => 'u',
        'ü' => 'u',
        'ï' => 'i',
        'ô' => 'o',
        'ö' => 'o',
        'ó' => 'o',
        'î' => 'i',
        'ë' => 'e',
        'ê' => 'e',
        '[' => '(',
        ']' => ')',
        '{' => '(',
        '}' => ')',
        '`' => "'",
        '‘' => "'",
        'µ' => 'u',
        'Ä' => 'A',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'È' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ü' => 'U',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ý' => 'Y',
        'ä' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'Ö' => 'O',
        'Ô' => 'O',
        'œ' => 'oe',
        '^' => '',
        '~' => '',
        '|' => '',
        '\\' => '',
        '¨' => '',
        '°' => '.',
        '“' => "'",
    ];

    /**
     * @throws LsMessageException
     */
    public function __construct(
        private readonly string $name,
        private readonly string $phone,
        private readonly string $content,
        private readonly string $sender,
    ) {
        $this->checkName($name);
        $this->checkContent($content);
        $this->checkSender($sender);
    }

    /**
     * @throws LsMessageException
     */
    private function checkName(string $name): void
    {
        if (empty($name)) {
            throw new LsMessageException('Le nom ne doit pas être vide.', 400);
        }

        if (strlen($name) >= 255) {
            throw new LsMessageException('La longueur maximale du nom doit être inférieure à 255.', 400);
        }
    }

    /**
     * @throws LsMessageException
     */
    private function checkContent(string $content): void
    {
        if (empty($content)) {
            throw new LsMessageException('Le message ne doit pas être vide.', 400);
        }

        $nbContent = LsMessageWrapper::countGsmCharacters($content);
        if ($nbContent > self::MAX_CHARACTERS_MESSAGE) {
            throw new LsMessageException('Le message ne doit pas dépasser ' . self::MAX_CHARACTERS_MESSAGE . ' caractères.', 400);
        }
    }

    /**
     * @throws LsMessageException
     */
    private function checkSender(string $sender): void
    {
        if (empty($sender)) {
            throw new LsMessageException('L’expéditeur ne doit pas être vide.', 400);
        }

        if (!preg_match(self::REGEX_VALIDATION_SENDER, $sender)) {
            throw new LsMessageException('L’expéditeur ne peut pas contenir de caractères spéciaux ni d’espace. Il doit être composé uniquement de lettres ou de caractères alphanumériques (lettres et chiffres).', 400);
        }

        $nbSender = strlen($sender);
        if ($nbSender < self::MIN_SENDER) {
            throw new LsMessageException('L’expéditeur ne doit pas être inférieur à ' . self::MIN_SENDER . ' caractères.', 400);
        }

        if ($nbSender > self::MAX_SENDER) {
            throw new LsMessageException('L’expéditeur ne doit pas dépasser ' . self::MAX_SENDER . ' caractères.', 400);
        }
    }

    /**
     * @return array{name: string, phone: string, content: string, sender: string}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'content' => $this->content,
            'sender' => $this->sender,
        ];
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
