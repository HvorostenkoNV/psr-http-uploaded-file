<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFile;

use InvalidArgumentException;
use Psr\Http\Message\{
    StreamInterface,
    UploadedFileFactoryInterface,
    UploadedFileInterface,
};

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size                  = null,
        ?int $error                 = UPLOAD_ERR_OK,
        ?string $clientFilename     = null,
        ?string $clientMediaType    = null
    ): UploadedFileInterface {
        try {
            $errorCase = UploadedFileError::from($error);
        } catch (InvalidArgumentException) {
            $errorCase = UploadedFileError::OK;
        }

        return new UploadedFile(
            $stream,
            $size,
            $errorCase,
            $clientFilename,
            $clientMediaType
        );
    }
}
