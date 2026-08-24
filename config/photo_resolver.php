<?php

const AUTO_OFFICER_PHOTO_DIRECTORY = 'D:\\PHOTOS\\id_number';
const LOCAL_OFFICER_PHOTO_DIRECTORY = 'D:\\PHOTOS';

/**
 * Resolve an officer photo without exposing a local filesystem path.
 * Priority: configured database path, then automatic NIC/officer-ID matching
 * in D:\PHOTOS\id_number and D:\PHOTOS. Local paths are never exposed.
 */
function resolve_officer_photo(array $officer): ?string
{
    $configuredPath = trim((string) ($officer['photo'] ?? ''));
    if ($configuredPath !== '' && is_file($configuredPath)) {
        return realpath($configuredPath) ?: $configuredPath;
    }

    $candidateNames = array_filter([
        normalize_photo_identifier($officer['nic'] ?? ''),
        normalize_photo_identifier($officer['officer_id'] ?? ''),
        normalize_photo_identifier($officer['id'] ?? ''),
    ]);

    foreach ([AUTO_OFFICER_PHOTO_DIRECTORY, LOCAL_OFFICER_PHOTO_DIRECTORY] as $directory) {
        $automaticPhoto = find_photo_by_identifier($directory, $candidateNames);
        if ($automaticPhoto !== null) {
            return $automaticPhoto;
        }
    }

    if ($configuredPath !== '') {
        $localCopy = LOCAL_OFFICER_PHOTO_DIRECTORY . DIRECTORY_SEPARATOR . basename(str_replace('\\', '/', $configuredPath));
        if (is_file($localCopy)) {
            return realpath($localCopy) ?: $localCopy;
        }
    }

    return null;
}

function normalize_photo_identifier($value): string
{
    return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', trim((string) $value)));
}

function find_photo_by_identifier(string $directory, array $identifiers): ?string
{
    if (!is_dir($directory) || $identifiers === []) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $iterator = new DirectoryIterator($directory);

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, $allowedExtensions, true)) {
            continue;
        }

        $fileIdentifier = normalize_photo_identifier($file->getBasename('.' . $file->getExtension()));
        if (in_array($fileIdentifier, $identifiers, true)) {
            return $file->getRealPath() ?: $file->getPathname();
        }
    }

    return null;
}
