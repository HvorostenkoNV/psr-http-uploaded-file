<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests\Generator;

use HNV\Http\UploadedFile\UploadedFileError;

class GeneratedUploadedFileData
{
    public function __construct(
        public readonly string $name,
        public readonly string $mimeType,
        public readonly string $tmpName,
        public readonly UploadedFileError $error,
        public readonly int $size
    ) {
    }
}
