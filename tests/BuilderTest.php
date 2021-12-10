<?php

declare(strict_types=1);

namespace Ysato\HttpClientBuilder;

use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private Builder $SUT;

    protected function setUp(): void
    {
        $this->SUT = new Builder();
        $this->SUT->getHttpClient();
    }

    public function testGetHttpClientCallTwice(): void
    {
        $httpClient = $this->SUT->getHttpClient();

        $this->assertSame($httpClient, $this->SUT->getHttpClient());
        $this->assertFalse($this->SUT->isHttpClientModified());
    }

    public function testAddPlugin(): void
    {
        $plugin = new FakePlugin();

        $this->SUT->addPlugin($plugin);

        $this->assertSame([$plugin], $this->SUT->getPlugins());
        $this->assertTrue($this->SUT->isHttpClientModified());
    }

    public function testRemovePlugin(): void
    {
        $this->SUT->addPlugin(new FakePlugin());

        $this->SUT->removePlugin(FakePlugin::class);

        $this->assertSame([], $this->SUT->getPlugins());
        $this->assertTrue($this->SUT->isHttpClientModified());
    }

    public function testAddHeaders(): void
    {
        $this->SUT->addHeaders(['Accept' => 'application/json', 'Connection' => 'keep-alive']);

        $this->assertSame(['Accept' => 'application/json', 'Connection' => 'keep-alive'], $this->SUT->getHeaders());
        $this->assertTrue($this->SUT->isHttpClientModified());
    }

    public function testAddHeaderValue(): void
    {
        $this->SUT->addHeaderValue('Accept', 'application/json');

        $this->assertSame(['Accept' => 'application/json'], $this->SUT->getHeaders());
        $this->assertTrue($this->SUT->isHttpClientModified());
    }

    public function testClearHeaders(): void
    {
        $this->SUT->clearHeaders();

        $this->assertSame([], $this->SUT->getHeaders());
        $this->assertTrue($this->SUT->isHttpClientModified());
    }
}
