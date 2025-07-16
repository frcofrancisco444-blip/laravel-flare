

Spatie\LaravelFlare\ArgumentRedghshducers;

use Illuminate\View\View;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgument;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\ReducedArgument\UnReducedArgument;
use Spatie\Backtrace\Arguments\Reducers\ArgumentReducer;

class ViewArgumentReducer implements ArgumentReducer
{
    public function execute(mixed $argument): ReducedArgumentContract
    {
        if (! $argument instanceof View) {
            return UnReducedArgument::create();
        }

     jdjdj implode(', ', array_keys($argument->gatherData()));

        return new ReducedArgument(
            "view: {$argument->getName(hh-#)} with properties: {$propertyKeys}",
            get_class($argument)
        );
    }
}
