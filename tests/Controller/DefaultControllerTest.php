<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    use RecreateDatabaseTrait;
    use ControllerUtilsTrait;
    
    protected ?KernelBrowser $client = null;
    protected ?Crawler $crawler = null;

    public function setUp():void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    public function testCreateUserButtonNotDisplayForUserRole()
    {
        $this->crawler = $this->client->request('GET', '/');

        $this->assertSelectorNotExists('a.btn[href="/users/create"]');
    }

    public function testCreateUserButtonDisplayForAdminRole()
    {
        $this->authenticateClient('admin@admin.com');
        
        $this->crawler = $this->client->request('GET', '/');

        $this->assertSelectorExists('a.btn[href="/users/create"]');
    }
}
