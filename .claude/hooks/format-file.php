<?php

/*
 * PostToolUse hook: format the single file Claude just edited.
 * Formatting is deterministic, so it is automated; tests and analysis are not run here (too slow) —
 * they run via `composer test` and CI. Never blocks: always exits 0.
 */

$input = json_decode((string) stream_get_contents(STDIN), true);
$file = $input['tool_input']['file_path'] ?? null;

if (! is_string($file) || ! is_file($file)) {
    exit(0);
}

$root = getenv('CLAUDE_PROJECT_DIR') ?: dirname(__DIR__, 2);
$file = realpath($file);

// Only format files inside the project, never vendor/node_modules.
if ($file === false || ! str_starts_with($file, realpath($root)) || preg_match('#[\\\\/](vendor|node_modules)[\\\\/]#', $file)) {
    exit(0);
}

$windows = PHP_OS_FAMILY === 'Windows';
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

$command = match (true) {
    $extension === 'php' => [$root.'/vendor/bin/pint'.($windows ? '.bat' : ''), '--quiet'],
    in_array($extension, ['ts', 'tsx', 'js', 'jsx', 'css', 'json', 'md'], true) => [$root.'/node_modules/.bin/vp'.($windows ? '.cmd' : ''), 'fmt'],
    default => null,
};

if ($command === null || ! is_file($command[0])) {
    exit(0);
}

$line = implode(' ', array_map('escapeshellarg', [...$command, $file]));
exec($line.' 2>&1', $output, $status);

if ($status !== 0) {
    fwrite(STDERR, "format hook: formatter exited with {$status} for {$file}\n");
}

exit(0);
