<?php


use Libriciel\LsMessageWrapper\LsMessageException;
use Libriciel\LsMessageWrapper\Sms;
use PHPUnit\Framework\TestCase;

class SmsTest extends TestCase
{

    public function testTooLongOrEmpty()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('max name length must be < 255 and not empty');
        new Sms("", '0602030405', 'ceci est le contenu', 'sender');
    }

    public function testNotACorrectPhoneNumber()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('phone must be 06XXXXXXXX or 07XXXXXXXX');
        new Sms("name", '0102030405', 'ceci est le contenu', 'sender');
    }

    public function testNoContent()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('message must by < 159 characters');
        new Sms("name", '0602030405', '', 'sender');
    }


    public function testSenderTooLong()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender must by < 11 characters');
        new Sms("name", '0602030405', 'content', 'senderTooLong');
    }

    public function testSenderWithSpecialCharacters()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender cannot have special characters');
        new Sms("name", '0602030405', 'content', 'i-delibre');
    }

    public function testSenderAcceptAlphaNumeric()
    {
        $sms = new Sms("name", '0602030405', 'content', 'Ide12');
        $this->assertSame('Ide12', $sms->getSender());
    }


    public function testJsonSerialize()
    {
        $sms = new Sms("name", '0602030405', 'content', 'sender');
        $json = $sms->jsonSerialize();
        $this->assertSame('0602030405', $json['phone']);
    }

    public function testGetter(){
        $sms = new Sms("name", '0602030405', 'content', 'sender');
        $this->assertSame('name', $sms->getName());
        $this->assertSame('0602030405', $sms->getPhone());
        $this->assertSame('content', $sms->getContent());
        $this->assertSame('sender', $sms->getSender());
    }
}
