<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Function-style Pest tests in Feature/ get the Laravel TestCase and a fresh database.
 * The starter kit's class-based PHPUnit tests keep working unchanged.
 */
pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
