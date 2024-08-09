<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelFlare\AttributesProviders\LivewireAttributesProvider;
use Spatie\LaravelFlare\ContextProviders\LaravelLivewireRequestContextProvider;
use Spatie\LaravelFlare\Tests\TestClasses\FakeLivewireManager;

beforeEach(function () {
    $this->livewireManager = resolve(FakeLivewireManager::class);
});

it('returns the referer url and method', function () {
    $attributes = createRequestPayload([
        'path' => 'referred',
        'method' => 'GET',
    ]);

    expect($attributes['url.full'])->toBe('http://localhost/POST');
    expect($attributes['http.request.method'])->toBe('POST');
});

it('returns livewire component information', function () {
    $alias = 'fake-component';
    $class = 'fake-class';

    $this->livewireManager->addAlias($alias, $class);

    $attributes = createRequestPayload([
        'path' => 'http://localhost/referred',
        'method' => 'GET',
        'id' => $id = uniqid(),
        'name' => $alias,
    ]);

    $livewire = $attributes['livewire.components'];

    expect($livewire[0]['component_id'])->toBe($id);
    expect($livewire[0]['component_alias'])->toBe($alias);
});

it('returns livewire component information when it does not exist', function () {
    $attributes = createRequestPayload([
        'path' => 'http://localhost/referred',
        'method' => 'GET',
        'id' => $id = uniqid(),
        'name' => $name = 'fake-component',
    ]);

    $livewire = $attributes['livewire.components'];

    expect($livewire[0]['component_id'])->toBe($id);
    expect($livewire[0]['component_alias'])->toBe($name);
    expect($livewire[0]['component_class'])->toBeNull();
});

it('removes ids from update payloads', function () {
    $attributes = createRequestPayload([
        'path' => 'http://localhost/referred',
        'method' => 'GET',
        'id' => $id = uniqid(),
        'name' => $name = 'fake-component',
    ], [
        [
            'type' => 'callMethod',
            'payload' => [
                'id' => 'remove-me',
                'method' => 'chang',
                'params' => ['a'],
            ],
        ],
    ]);

    $livewire = $attributes['livewire.components'];

    expect($livewire[0]['component_id'])->toBe($id);
    expect($livewire[0]['component_alias'])->toBe($name);
    expect($livewire[0]['component_class'])->toBeNull();
});

it('combines data into one payload', function () {
    $attributes = createRequestPayload([
        'path' => 'http://localhost/referred',
        'method' => 'GET',
        'id' => uniqid(),
        'name' => 'fake-component',
    ], [], [
        'data' => [
            'string' => 'Ruben',
            'array' => ['a', 'b'],
            'modelCollection' => [],
            'model' => [],
            'date' => '2021-11-10T14:20:36+0000',
            'collection' => ['a', 'b'],
            'stringable' => 'Test',
            'wireable' => ['a', 'b'],
        ],
        'dataMeta' => [
            'modelCollections' => [
                'modelCollection' => [
                    'class' => 'App\\\\Models\\\\User',
                    'id' => [1, 2, 3, 4],
                    'relations' => [],
                    'connection' => 'mysql',
                ],
            ],
            'models' => [
                'model' => [
                    'class' => 'App\\\\Models\\\\User',
                    'id' => 1,
                    'relations' => [],
                    'connection' => 'mysql',
                ],
            ],
            'dates' => [
                'date' => 'carbonImmutable',
            ],
            'collections' => [
                'collection',
            ],
            'stringables' => [
                'stringable',
            ],
            'wireables' => [
                'wireable',
            ],
        ],
    ]);

    $livewire = $attributes['livewire.components'];

    $this->assertEquals([
        "string" => "Ruben",
        "array" => ['a', 'b'],
        "modelCollection" => [
            "class" => "App\\\\Models\\\\User",
            "id" => [1, 2, 3, 4],
            "relations" => [],
            "connection" => "mysql",
        ],
        "model" => [
            "class" => "App\\\\Models\\\\User",
            "id" => 1,
            "relations" => [],
            "connection" => "mysql",
        ],
        "date" => "2021-11-10T14:20:36+0000",
        "collection" => ['a', 'b'],
        "stringable" => "Test",
        "wireable" => ['a', 'b'],
    ], $livewire[0]['data']);
});

// Helpers
function createRequestPayload(array $fingerprint, array $updates = [], array $serverMemo = []): array
{
    $providedRequest = null;

    Route::post('livewire', function (Request $request) use (&$providedRequest) {
        $providedRequest = $request;
    })->name('livewire.message');

    test()->postJson('livewire', [
        'fingerprint' => $fingerprint,
        'serverMemo' => $serverMemo,
        'updates' => $updates,
    ], ['X-Livewire' => 1]);

    return (new LivewireAttributesProvider())->toArray($providedRequest, test()->livewireManager);
}
