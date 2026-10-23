<?php

/*
 * Structural rules from docs/architecture/overview.md.
 * Tenancy rules (BelongsToSchool on every tenant model) are added in TEN-002.
 */

arch('no debugging or unsafe PHP functions')
    ->preset()->php();

arch('no Laravel debug helpers left in code')
    ->expect(['dd', 'dump', 'ddd', 'ray'])
    ->not->toBeUsed();

arch('no insecure functions (md5, rand, eval, exec, ...)')
    ->preset()->security();

arch('controllers do not query the database directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

arch('domain code does not depend on the HTTP layer')
    ->expect('App\Domain')
    ->not->toUse('App\Http');
