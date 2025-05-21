<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminChatControllerTest extends WebTestCase{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/chat');

        self::assertResponseIsSuccessful();
    }
}
