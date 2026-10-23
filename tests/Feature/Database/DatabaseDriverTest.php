<?php

use Illuminate\Support\Facades\DB;

/*
 * Guard: in CI the test process itself must run on PostgreSQL. `artisan db:show` in the workflow
 * only proves the artisan process does; a later force="true" in phpunit.xml would silently send
 * the suite back to SQLite. GitHub Actions sets CI=true.
 */
test('the test suite runs on PostgreSQL in CI', function () {
    expect(DB::connection()->getDriverName())->toBe('pgsql');
})->skip(fn () => ! env('CI'), 'Only enforced in CI until local PostgreSQL exists (FND-010).');
