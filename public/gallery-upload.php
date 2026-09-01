<?php

declare(strict_types=1);

use SentryIQCloud\Contracts\AuthenticationInterface;
use SentryIQCloud\Contracts\CsrfTokenInterface;
use SentryIQCloud\Gallery\Image\ImageProcessor;
use SentryIQCloud\Gallery\Image\ThumbnailGenerator;
use SentryIQCloud\Gallery\Storage\DuplicateIndex;
use SentryIQCloud\Gallery\Storage\PhotoStorage;
use SentryIQCloud\Gallery\UploadService;

require dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'POST required.']);
    exit;
}

/**
 * The host application must provide these dependencies from its authenticated
 * SentryIQ session. Cloud deliberately does not create a second credential
 * or session system for the Gallery.
 */
$auth = $GLOBALS['sentryIqCloudAuthentication'] ?? null;
$csrf = $GLOBALS['sentryIqCloudCsrf'] ?? null;

if (!$auth instanceof AuthenticationInterface || !$csrf instanceof CsrfTokenInterface) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Gallery authentication is not configured.']);
    exit;
}

if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
if (!is_string($csrfToken) || !$csrf->isValid($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token.']);
    exit;
}

$files = $_FILES['photos'] ?? null;
if (!is_array($files) || !isset($files['name'], $files['tmp_name'], $files['error'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No photos were supplied.']);
    exit;
}

$names = is_array($files['name']) ? $files['name'] : [$files['name']];
$tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
$errors = is_array($files['error']) ? $files['error'] : [$files['error']];

$storageRoot = dirname(__DIR__) . '/storage/gallery';
$service = new UploadService(
    new ImageProcessor(),
    new ThumbnailGenerator(),
    new DuplicateIndex($storageRoot . '/duplicate-index.json'),
    new PhotoStorage($storageRoot),
);

$results = [];
foreach ($errors as $index => $error) {
    $results[] = $service->upload([
        'name' => $names[$index] ?? '',
        'tmp_name' => $tmpNames[$index] ?? '',
        'error' => $error,
    ]);
}

echo json_encode([
    'status' => 'complete',
    'results' => $results,
], JSON_UNESCAPED_SLASHES);
