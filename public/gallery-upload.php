<?php

declare(strict_types=1);

use SentryIQCloud\Gallery\Image\ImageProcessor;
use SentryIQCloud\Gallery\Image\ThumbnailGenerator;
use SentryIQCloud\Gallery\Storage\DuplicateIndex;
use SentryIQCloud\Gallery\Storage\PhotoStorage;
use SentryIQCloud\Gallery\UploadService;
use SentryIQCloud\Integration\SentryIQ\SentryIQSession;

require dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'POST required.']);
    exit;
}

try {
    // The host SentryIQ application must bootstrap its existing secure session
    // before this endpoint is reached. Cloud does not create another session.
    $sentryIqSession = new SentryIQSession();
} catch (Throwable $exception) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gallery authentication is not configured.',
    ]);
    exit;
}

if (!$sentryIqSession->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
if (!is_string($csrfToken) || !$sentryIqSession->isValid($csrfToken)) {
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
    'user' => $sentryIqSession->userId(),
    'results' => $results,
], JSON_UNESCAPED_SLASHES);
