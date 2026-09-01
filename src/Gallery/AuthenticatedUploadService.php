<?php

declare(strict_types=1);

namespace SentryIQCloud\Gallery;

use SentryIQCloud\Contracts\AuthenticationInterface;

final class AuthenticatedUploadService
{
    public function __construct(
        private readonly AuthenticationInterface $authentication,
        private readonly UploadService $uploads,
    ) {
    }

    /**
     * @param array{name?:string, tmp_name?:string, error?:int, size?:int} $upload
     * @return array{status:string, photo_id?:string, hash?:string, path?:string, thumbnail_path?:string, message:string}
     */
    public function upload(array $upload): array
    {
        if (!$this->authentication->isAuthenticated()) {
            return [
                'status' => 'unauthorized',
                'message' => 'Authentication is required to upload gallery images.',
            ];
        }

        return $this->uploads->upload($upload);
    }

    public function userId(): string
    {
        return $this->authentication->userId();
    }
}
