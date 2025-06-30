<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        self::assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
}
