<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests\Generator;

use HNV\Http\UploadedFile\UploadedFileError;

/**
 * Generated uploaded file value object.
 */
class GeneratedUploadedFileData
{
    /**
     * Constructor.
     */
    public function __construct(
        private readonly string $name,
        private readonly string $mimeType,
        private readonly string $tmpName,
        private readonly UploadedFileError $error,
        private readonly int $size
    ) {
    }

    /**
     * Get file name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get file MIME type.
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Get file temporary name.
     */
    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    /**
     * Get file error.
     */
    public function getError(): UploadedFileError
    {
        return $this->error;
    }

    /**
     * Get file size.
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
