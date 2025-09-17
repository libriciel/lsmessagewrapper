<?php

use Libriciel\LsMessageWrapper\LsMessageException;
use Libriciel\LsMessageWrapper\Sms;
use PHPUnit\Framework\TestCase;

class SmsTest extends TestCase
{
    public function testNameEmpty(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Le nom ne doit pas être vide.');

        new Sms("", '0602030405', 'ceci est le contenu', 'sender');
    }

    public function testNameTooLong(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('La longueur maximale du nom doit être inférieure à 255.');

        new Sms(
            "A while back I needed to count the amount of letters that a piece of text in an email template had (to avoid passing any character limits). Unfortunately, I could not think of a quick way to do so on my macbook and I therefore turned to the Internet. There were a couple of tools out there, but none of them met my standards and since I am a web designer I thought: why not do it myself and help others along the way? And... here is the result, hope it helps out!",
            '0602030405',
            'ceci est le contenu',
            'sender'
        );
    }

    public function testContentEmpty(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Le message ne doit pas être vide.');

        new Sms("name", '0602030405', '', 'sender');
    }

    public function testContentTooLong()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Le message ne doit pas dépasser 160 caractères.');
        new Sms(
            "name",
            '0602030405',
            'A while back I needed to count the amount of letters that a piece of text in an email template had (to avoid passing any character limits). Unfortunately, I could not think of a quick way to do so on my macbook and I therefore turned to the Internet.',
            'sender'
        );
    }

    public function testSenderEmpty(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('L’expéditeur ne doit pas être vide.');

        new Sms("name", '0602030405', 'content', '');
    }

    public function testSenderTooShort(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('L’expéditeur ne doit pas être inférieur à 3 caractères.');
        new Sms("name", '0602030405', 'content', 'se');
    }

    public function testSenderTooLong(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('L’expéditeur ne doit pas dépasser 11 caractères.');
        new Sms("name", '0602030405', 'content', 'senderTooLong');
    }

    public function testSenderWithSpecialCharacters(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('L’expéditeur ne peut pas contenir de caractères spéciaux ni d’espace. Il doit être composé uniquement de lettres ou de caractères alphanumériques (lettres et chiffres).');

        new Sms("name", '0602030405', 'content', 'i-de!ibre');
    }

    public function testSenderWithSpecialCharactersSpace(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('L’expéditeur ne peut pas contenir de caractères spéciaux ni d’espace. Il doit être composé uniquement de lettres ou de caractères alphanumériques (lettres et chiffres).');

        new Sms("name", '0602030405', 'content', 'i deibre');
    }

    public function testSenderAcceptAlphaNumeric(): void
    {
        $sms = new Sms("name", '0602030405', 'content', '1Ide12');
        $this->assertSame('1Ide12', $sms->getSender());
    }

    public function testJsonSerialize(): void
    {
        $sms = new Sms("name", '0602030405', 'content', 'sender');
        $json = $sms->jsonSerialize();
        $this->assertSame('0602030405', $json['phone']);
    }

    public function testGetter(): void
    {
        $sms = new Sms("name", '0602030405', 'content', 'sender');
        $this->assertSame('name', $sms->getName());
        $this->assertSame('0602030405', $sms->getPhone());
        $this->assertSame('content', $sms->getContent());
        $this->assertSame('sender', $sms->getSender());
    }
}
