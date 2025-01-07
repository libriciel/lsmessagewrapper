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
        $this->expectExceptionMessage('name must be not empty');

        new Sms("", '0602030405', 'ceci est le contenu', 'sender');
    }

    public function testNameTooLong(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('max name length must be not exceed 255 characters');

        new Sms(
            "A while back I needed to count the amount of letters that a piece of text in an email template had (to avoid passing any character limits). Unfortunately, I could not think of a quick way to do so on my macbook and I therefore turned to the Internet. There were a couple of tools out there, but none of them met my standards and since I am a web designer I thought: why not do it myself and help others along the way? And... here is the result, hope it helps out!",
            '0602030405',
            'ceci est le contenu',
            'sender'
        );
    }

    public function testPhoneNumberEmpty(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('phone must be not empty');

        new Sms("name", '', 'ceci est le contenu', 'sender');
    }

    public function testNotACorrectPhoneNumber(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('phone must be 06XXXXXXXX or 07XXXXXXXX');

        new Sms("name", '0102030405', 'ceci est le contenu', 'sender');
    }

    public function testContentEmpty(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('message must be not empty');

        new Sms("name", '0602030405', '', 'sender');
    }

    public function testContentTooLong()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('message must be not exceed 160 characters');
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
        $this->expectExceptionMessage('sender must be not empty');

        new Sms("name", '0602030405', 'content', '');
    }

    public function testSenderTooShort(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender must not be less than 3 characters');
        new Sms("name", '0602030405', 'content', 'se');
    }

    public function testSenderTooLong(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender must be not exceed 11 characters');
        new Sms("name", '0602030405', 'content', 'senderTooLong');
    }

    public function testSenderWithSpecialCharacters(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender cannot have special characters or space. Be composed only of alphabetic (letters) or alphanumeric (letters and numbers) characters.');

        new Sms("name", '0602030405', 'content', 'i-de!ibre');
    }

    public function testSenderWithSpecialCharactersSpace(): void
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender cannot have special characters or space. Be composed only of alphabetic (letters) or alphanumeric (letters and numbers) characters.');

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
