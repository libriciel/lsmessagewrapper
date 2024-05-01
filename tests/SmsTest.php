<?php


use Libriciel\LsMessageWrapper\LsMessageException;
use Libriciel\LsMessageWrapper\Sms;
use PHPUnit\Framework\TestCase;

class SmsTest extends TestCase
{

    public function testNameEmpty()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('message must be not empty');
        new Sms("", '0602030405', 'ceci est le contenu', 'sender');
    }

    public function testNameTooLong()
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

    public function testNotACorrectPhoneNumber()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('phone must be 06XXXXXXXX or 07XXXXXXXX');
        new Sms("name", '0102030405', 'ceci est le contenu', 'sender');
    }

    public function testContentEmpty()
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

    public function testSenderEmpty()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender must be not empty');
        new Sms("name", '0602030405', 'content', '');
    }

    public function testSenderTooShort()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender must not be less than 3 characters');
        new Sms("name", '0602030405', 'content', 'se');
    }

    public function testSenderTooLong()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender must be not exceed 11 characters');
        new Sms("name", '0602030405', 'content', 'senderTooLong');
    }

    public function testSenderWithSpecialCharacters()
    {
        $this->expectException(LsMessageException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('sender cannot have special characters');
        new Sms("name", '0602030405', 'content', 'i-de!ibre');
    }

    public function testSenderAcceptAlphaNumeric()
    {
        $sms = new Sms("name", '0602030405', 'content', '1Ide12');
        $this->assertSame('1Ide12', $sms->getSender());
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
