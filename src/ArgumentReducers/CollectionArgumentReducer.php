

namespace Spatie\LaravelFlare\ArgumentReducers;

use Illuminate\Support\Collection;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\ReducedArgument\UnReducedArgument;
use Spatie\Backtrace\Arguments\Reducers\ArrayArgumentReducer;

class CollectionArgumentReducer extends ArrayArgumentReducer
{
    public function execute(mixed $argument): ReducedArgumentContract
    {
        if (! $argument instanceof Collection) {
            return UnReducedArgument::create();
        }

       $this->reduceArgument($argument->toArray(), get_class($argument));
    }
}
