<?php

use Tests\TestCase;

/*
 * Function-style Pest tests in Feature/ get the Laravel TestCase.
 * The starter kit's class-based PHPUnit tests keep working unchanged.
 */
pest()->extend(TestCase::class)->in('Feature');
