<?php

use Illuminate\Contracts\Console\Kernel;
use Spatie\FlareClient\Enums\SpanType;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Tests\Shared\ExpectSpan;
use Spatie\FlareClient\Tests\Shared\ExpectTrace;
use Spatie\FlareClient\Tests\Shared\ExpectTracer;
use Spatie\FlareClient\Tests\Shared\FakeIds;
use Spatie\FlareClient\Tests\Shared\FakeTime;
use Spatie\LaravelFlare\Support\TracingKernel;
use Spatie\LaravelFlare\Tests\Concerns\ConfigureFlare;
use Spatie\LaravelFlare\Tests\stubs\Commands\TestCommand;
use Spatie\LaravelFlare\Tests\stubs\Exceptions\ExpectedException;

uses(ConfigureFlare::class)->beforeEach(function () {
    FakeIds::setup();
    FakeTime::setup('2019-01-01 12:34:56');

    TracingKernel::$run = false;

    $consoleKernel = app(Kernel::class);
    $consoleKernel->addCommands([TestCommand::class]);
    $consoleKernel->rerouteSymfonyCommandEvents(); // make sure events are triggered

    test()->consoleKernel = $consoleKernel;
    test()->flare = setupFlareForTracing(runKernelCallbacks: true);
});

it('can report a command', function () {
    /** @var Flare $flare */
    $flare = test()->flare;

    test()->consoleKernel->call('flare:test-command');

    $report = $flare->report(
        new ExpectedException('This is a test exception'),
    );

    expect($report->toArray()['events'])->toHaveCount(1);

    expect($report->toArray()['events'][0])
        ->toHaveKey('startTimeUnixNano', 1546346096000000000)
        ->toHaveKey('endTimeUnixNano', 1546346096000000000)
        ->toHaveKey('type', SpanType::Command);


    expect($report->toArray()['events'][0]['attributes'])
        ->toHaveCount(3)
        ->toHaveKey('process.command', 'flare:test-command')
        ->toHaveKey('process.command_args', ["flare:test-command", "with-default"])
        ->toHaveKey('process.exit_code', 0);
});

it('can trace a command', function () {
    test()->flare->tracer->startTrace();

    test()->consoleKernel->call('flare:test-command');

    ExpectTracer::create(test()->flare)
        ->isSampling()
        ->hasTraceCount(1)
        ->trace(
            fn (ExpectTrace $trace) => $trace
                ->hasSpanCount(1)
                ->span(
                    fn (ExpectSpan $span) => $span
                        ->hasName('Command - flare:test-command')
                        ->hasType(SpanType::Command)
                        ->isEnded()
                        ->hasAttributeCount(4)
                        ->hasAttribute('process.command', 'flare:test-command')
                        ->hasAttribute('process.command_args', ["flare:test-command", "with-default"])
                        ->hasAttribute('process.exit_code', 0)
                )
        );
});

it('can trace a command with options and arguments', function () {
    test()->flare->tracer->startTrace();

    test()->consoleKernel->call('flare:test-command --option=something --boolean-option some-argument');

    ExpectTracer::create(test()->flare)
        ->isSampling()
        ->hasTraceCount(1)
        ->trace(
            fn (ExpectTrace $trace) => $trace
                ->hasSpanCount(1)
                ->span(
                    fn (ExpectSpan $span) => $span
                        ->hasName('Command - flare:test-command')
                        ->hasType(SpanType::Command)
                        ->isEnded()
                        ->hasAttributeCount(4)
                        ->hasAttribute('process.command', 'flare:test-command')
                        ->hasAttribute('process.command_args', ["flare:test-command", "some-argument", "--option=something", "--boolean-option"])
                        ->hasAttribute('process.exit_code', 0)
                )
        );
});

it('can trace a failed command', function () {
    test()->flare->tracer->startTrace();

    try {
        test()->consoleKernel->call('flare:test-command --should-fail');
    } catch (ExpectedException) {

    }

    ExpectTracer::create(test()->flare)
        ->isSampling()
        ->hasTraceCount(1)
        ->trace(
            fn (ExpectTrace $trace) => $trace
                ->hasSpanCount(1)
                ->span(
                    fn (ExpectSpan $span) => $span
                        ->hasAttribute('process.exit_code', 1)
                )
        );
});

it('can trace a nested command which will be added to the same trace', function () {
    test()->flare->tracer->startTrace();

    test()->consoleKernel->call('flare:test-command --run-nested');

    ExpectTracer::create(test()->flare)
        ->isSampling()
        ->hasTraceCount(1)
        ->trace(
            fn (ExpectTrace $trace) => $trace
                ->hasSpanCount(2)
                ->span(
                    fn (ExpectSpan $span) => $span
                        ->hasName('Command - flare:test-command')
                        ->hasType(SpanType::Command)
                        ->isEnded()
                        ->hasAttributeCount(4)
                        ->hasAttribute('process.command', 'flare:test-command')
                        ->hasAttribute('process.command_args', ["flare:test-command", "with-default", "--run-nested"])
                        ->hasAttribute('process.exit_code', 0),
                    $parentSpan
                )
                ->span(
                    fn (ExpectSpan $childSpan) => $childSpan
                        ->hasName('Command - flare:test-command')
                        ->hasType(SpanType::Command)
                        ->isEnded()
                        ->hasAttributeCount(4)
                        ->hasParent($parentSpan)
                        ->hasAttribute('process.command', 'flare:test-command')
                        ->hasAttribute('process.command_args', ['nested-argument', "flare:test-command", '--option=nested', '--boolean-option'])
                        ->hasAttribute('process.exit_code', 0)
                )
        );
});
