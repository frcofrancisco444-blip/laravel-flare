<?php

namespace Spatie\LaravelFlare\Tests;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Http\Request;
use Spatie\FlareClient\Glows\Glow;
use Spatie\FlareClient\Performance\Spans\Span;
use Spatie\FlareClient\Performance\Spans\SpanEvent;
use Spatie\FlareClient\Report;
use Spatie\LaravelFlare\Facades\Flare;
use Spatie\LaravelFlare\FlareServiceProvider;
use Spatie\LaravelFlare\Tests\TestClasses\FakeTime;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use MakesHttpRequests;

    protected $fakeClient = null;

    protected function setUp(): void
    {
        // ray()->newScreen($this->getName());

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        config()->set('flare.key', 'dummy-key');

        return [FlareServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Flare' => Flare::class,
        ];
    }

    public function useTime(string $dateTime, string $format = 'Y-m-d H:i:s')
    {
        $fakeTime = new FakeTime($dateTime, $format);

        Report::useTime($fakeTime);
        Span::useTime($fakeTime);
        SpanEvent::useTime($fakeTime);
    }

    public function createRequest($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): Request
    {
        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));

        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content
        );

        return Request::createFromBase($symfonyRequest);
    }
}
