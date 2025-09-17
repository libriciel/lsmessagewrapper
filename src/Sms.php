<?php

namespace Libriciel\LsMessageWrapper;

use JsonSerializable;

class Sms implements JsonSerializable
{
    public const MAX_CHARACTERS_MESSAGE = 160;
    public const MIN_SENDER = 3;
    public const MAX_SENDER = 11;
    public const REGEX_VALIDATION_SENDER = '/^[a-zA-Z0-9]+$/';
    public const REMPLACEMENTS_CARACTERES = [
        'ÿ' => 'y',
        'y' => 'y',
        'ç' => 'c',
        'Ç' => 'C',
        'û' => 'u',
        'u' => 'u',
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
        '`' => '‘',
        '‘' => '‘',
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
        '“' => '‘'
    ];

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
        $this->checkContent($content);
        $this->checkSender($sender);

        $this->name = $name;
        $this->phone = $phone;
        $this->content = $content;
        $this->sender = $sender;
    }

    private function checkName(?string $name): void
    {
        if (empty($name)) {
            throw new LsMessageException("Le nom ne doit pas être vide.", 400);
        }

        if (strlen($name) >= 255) {
            throw new LsMessageException("La longueur maximale du nom doit être inférieure à 255.", 400);
        }
    }

    private function checkContent(?string $content): void
    {
        if (!isset($content) || !$content) {
            throw new LsMessageException("Le message ne doit pas être vide.",  400);
        }

        $nbContent = LsMessageWrapper::countGsmCharacters($content);
        if ($nbContent >= SMS::MAX_CHARACTERS_MESSAGE) {
            throw new LsMessageException("Le message ne doit pas dépasser " . SMS::MAX_CHARACTERS_MESSAGE . " caractères.", 400);
        }
    }

    private function checkSender(?string $sender): void
    {
        if (empty($sender)) {
            throw new LsMessageException("L’expéditeur ne doit pas être vide.", 400);
        }

        if (!preg_match(SMS::REGEX_VALIDATION_SENDER, $sender)) {
            throw new LsMessageException("L’expéditeur ne peut pas contenir de caractères spéciaux ni d’espace. Il doit être composé uniquement de lettres ou de caractères alphanumériques (lettres et chiffres).", 400);
        }

        $nbSender = strlen($sender);
        if ($nbSender < SMS::MIN_SENDER) {
            throw new LsMessageException("L’expéditeur ne doit pas être inférieur à " . SMS::MIN_SENDER . " caractères.", 400);
        }

        if ($nbSender > SMS::MAX_SENDER) {
            throw new LsMessageException("L’expéditeur ne doit pas dépasser " . SMS::MAX_SENDER . " caractères.", 400);
        }
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
