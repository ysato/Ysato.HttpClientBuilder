<?php

declare(strict_types=1);

namespace Ysato\HttpClientBuilder;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClientFactory;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function array_merge;

class Builder
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private bool $httpClientModified = true;

    /** @var Plugin[] */
    private array $plugins = [];

    /** @psalm-suppress PropertyNotSetInConstructor */
    private HttpMethodsClientInterface $pluginClient;

    /** @var array<string, string|array<string>> */
    private array $headers = [];

    public function __construct(
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    public function getHttpClient(): HttpMethodsClientInterface
    {
        if ($this->httpClientModified) {
            $this->httpClientModified = false;

            $plugins = $this->plugins;

            $this->pluginClient = new HttpMethodsClient(
                (new PluginClientFactory())->createClient($this->httpClient, $plugins),
                $this->requestFactory,
                $this->streamFactory
            );
        }

        return $this->pluginClient;
    }

    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[] = $plugin;
        $this->httpClientModified = true;
    }

    public function removePlugin(string $fqcn): void
    {
        foreach ($this->plugins as $idx => $plugin) {
            if (! ($plugin instanceof $fqcn)) {
                continue;
            }

            unset($this->plugins[$idx]);
            $this->httpClientModified = true;
        }
    }

    public function clearHeaders(): void
    {
        $this->headers = [];

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    /**
     * @param array<string, string|array<string>> $headers
     */
    public function addHeaders(array $headers): void
    {
        $this->headers = array_merge($this->headers, $headers);

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function addHeaderValue(string $header, string $headerValue): void
    {
        if (! isset($this->headers[$header])) {
            $this->headers[$header] = $headerValue;
        } else {
            $this->headers[$header] = array_merge((array) $this->headers[$header], [$headerValue]);
        }

        $this->removePlugin(Plugin\HeaderAppendPlugin::class);
        $this->addPlugin(new Plugin\HeaderAppendPlugin($this->headers));
    }

    public function isHttpClientModified(): bool
    {
        return $this->httpClientModified;
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @return array<string, string|array<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
