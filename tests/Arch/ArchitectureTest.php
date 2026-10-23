<?php

/*
 * Structural rules from docs/architecture/overview.md.
 * Tenancy rules (BelongsToSchool on every tenant model) are added in TEN-002;
 * module-to-module dependency rules are added with each module.
 */

arch('no debugging or unsafe PHP functions')
    ->preset()->php();

// The php preset does not cover Laravel's helpers (verified: a planted dd() passed it).
arch('no Laravel debug helpers left in code')
    ->expect(['dd', 'dump', 'ddd', 'ray'])
    ->not->toBeUsed();

arch('no insecure functions (md5, rand, eval, exec, ...)')
    ->preset()->security();

// Narrow on purpose: Eloquent in controllers is allowed for simple reads; workflows belong in Actions (review-enforced).
arch('controllers do not use the DB facade')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

// Vacuous until the first module lands in app/Domain.
arch('domain code does not depend on the HTTP layer')
    ->expect('App\Domain')
    ->not->toUse('App\Http');
