<?php

const DEFAULT_OFFICER_QR_DIRECTORY = 'D:\\PHOTOS\\QR';
const DEFAULT_OFFICER_PHOTO_DIRECTORY = 'D:\\PHOTOS\\id_number';
const DEFAULT_LEGACY_PHOTO_DIRECTORY = 'D:\\PHOTOS';
const LOCAL_SETTINGS_FILE = __DIR__ . '/local-settings.json';

function default_local_paths(): array
{
    return [
        'qr_directory' => DEFAULT_OFFICER_QR_DIRECTORY,
        'photo_directory' => DEFAULT_OFFICER_PHOTO_DIRECTORY,
    ];
}

function load_local_paths(): array
{
    $defaults = default_local_paths();
    if (!is_file(LOCAL_SETTINGS_FILE)) return $defaults;

    $decoded = json_decode((string) file_get_contents(LOCAL_SETTINGS_FILE), true);
    if (!is_array($decoded)) return $defaults;

    foreach ($defaults as $key => $default) {
        $value = trim((string) ($decoded[$key] ?? ''));
        if (is_absolute_windows_path($value)) $defaults[$key] = rtrim($value, "\\/");
    }
    return $defaults;
}

function local_path(string $key): string
{
    $paths = load_local_paths();
    return $paths[$key] ?? '';
}

function is_absolute_windows_path(string $path): bool
{
    return preg_match('/^[A-Za-z]:[\\\\\/](?!.*[<>"|?*])[^\r\n]+$/', trim($path)) === 1;
}

function save_local_paths(array $paths): bool
{
    $settings = default_local_paths();
    foreach ($settings as $key => $default) {
        $value = rtrim(trim((string) ($paths[$key] ?? '')), "\\/");
        if (!is_absolute_windows_path($value)) return false;
        $settings[$key] = $value;
    }

    $temporary = LOCAL_SETTINGS_FILE . '.tmp';
    $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($temporary, $json, LOCK_EX) === false) return false;
    return rename($temporary, LOCAL_SETTINGS_FILE);
}

function reset_local_paths(): bool
{
    return !is_file(LOCAL_SETTINGS_FILE) || unlink(LOCAL_SETTINGS_FILE);
}
