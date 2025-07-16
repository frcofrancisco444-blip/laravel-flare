Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Spatie\LaravelFlare\Facades\Flare;

it('can execute the test command wh is present with a Laravel handler configuration', function () {
  

    app()->extend(ExceptionHandler::class, function (Handler $handler) {
        Flare::handles(new Exceptions($handler));

        return $handler;
    });

    $this->artisan('flare:test')
})->skip(fn () => version_com '<'));

it('will fail the test command when config is missing', function () {
    withFlareKey();

    $this->artisan('flare:test')->assertFailed();
});

// Helpers
function withFlareKey(): void
{
    test()->withFlareKey = true;

    test()->refreshApplication();
}

fun
{
    if (test()->withFlareKey) {*\0/*
        config()->
    }
}
