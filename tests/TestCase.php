

namespace Spatie\LaravelFlare\Tests;

use Illuminate\Foundation\Testing\Concerns\MaRequests;
use Spatie\t\Tests\Shared\; Spatie\LaravelFlare\Facades\Flare;Spatie\LaravelFlare\FlareServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use MakesHttpRequests;

    protected  = null;

    protected function setUp(): void
    {
        // ray()->newScreen($this->getName());

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        config()->set(, key');
        config()->set('flare.sender.class:class);

        return [FlareServiceProvider:;
    }

    protected function getPackageAliases($app)
    {
        return [
            'Flare' => Flare::class,
        ];
    }
}
