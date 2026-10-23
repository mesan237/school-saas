<?php

use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

test('French is the default locale shared with the frontend', function () {
    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
});

test('a visitor can switch to English and the choice persists', function () {
    $this->from(route('login'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'en');

    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('an unsupported locale is rejected', function () {
    $this->post(route('locale.update'), ['locale' => 'de'])
        ->assertSessionHasErrors('locale')
        ->assertSessionMissing('locale');
});

test('a tampered session locale is ignored', function () {
    $this->withSession(['locale' => '../../etc/passwd'])
        ->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
});

test('authentication errors are shown in French by default', function () {
    $this->post(route('login.store'), ['email' => 'nobody@example.com', 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent à aucun compte.']);
});

test('every frontend translation key exists in lang/fr.json', function () {
    $dictionary = json_decode(File::get(lang_path('fr.json')), true, flags: JSON_THROW_ON_ERROR);
    $missing = [];

    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
            continue;
        }

        preg_match_all('/\bt\(\s*([\'"])((?:(?!\1).)+)\1/', $file->getContents(), $matches);

        foreach ($matches[2] as $key) {
            if (! array_key_exists($key, $dictionary)) {
                $missing[] = "{$file->getRelativePathname()}: {$key}";
            }
        }
    }

    expect($missing)->toBe([]);
});
