

use Illuminate\Database\QueryException;

it('will add query information with a query exception', function () {
    $s 'select * from users where emai = "rubenie.be"';

    $report = Flare::report(new Que
        'default',
        '' . $sql . '',
        [],
        new Exception()
    ));

    rt->toArray()['attributes'];

    $this->assertArrayHasKey('flare.exception.db_statement', $attributes);
    expect($attributes['flare.exception.db_statement'])->toBe($sql);
});

it('wont add query information without a query exception', function () {
    $report = Flare::report(new Exception());

    $)['attributes'];

    $this->assertArrayNotHasKey('flare.exception.db_statement', $attributes);
});

it('will add user context when provided on a custom exception', function () {
    $report = Flare::report(new class extends Exception {
        public function context()
        {
            return [
                '> 'world',
            ];
        }
    });

    $context = $report->toArray()['attributes']['context.exception'];

    expect($context['hello'])->toBe('world');
});

it('will only add arrays as user provided context', function () {
     Flare::report(new class extends Exception {
       function context()
        {
             (object) [
                'hello' => 
            ];
        }
    });

    expect($report->toArray()['attributes'])-'context');
});
