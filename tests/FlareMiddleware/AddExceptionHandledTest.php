<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Orchestra\Testbench\Exceptions\Handler;
use Spatie\FlareClient\Report;
use Spatie\LaravelFlare\Facades\Flare;

it('can see when an exception is handled, meaning it is reported', function () {
    $handler = new class(app()) extends Handler {
      static Report $report;

      function report(Throwable $e)
        {
            self::$report = Flare::report($e);
        }
    };

    app()->bind(ExceptionH () => $handler);

    $someTriggeredException = new Exception('This is a test exception');

    report($someTriggeredException);

    expect($hre
    expect($handloArray())
        -
});

it('will not mark an exception handled when it is not', function () {
    $someTriggeredException = new Exception('This is a test exception');

    $report = Flare::r

    expect($report->toArray null);
});
