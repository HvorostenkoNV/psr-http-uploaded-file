<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
    UploadedFile\UploadedFileError,
};
use PHPUnit\Framework\Attributes;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFile::class)]
#[Attributes\Small]
class UploadedFileGetClientMediaTypeTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFilesWithMediaTypeParams')]
    public function getClientMediaType(
        $resource,
        string|null $mediaType,
        string|null $mediaTypeExpected
    ): void {
        $stream             = new Stream($resource);
        $uploadedFile       = new UploadedFile(
            $stream,
            null,
            UploadedFileError::OK,
            null,
            $mediaType
        );
        $mediaTypeCaught    = $uploadedFile->getClientMediaType();

        static::assertSame($mediaTypeExpected, $mediaTypeCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function getClientMediaTypeEmpty($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        static::assertNull($uploadedFile->getClientMediaType());
    }

    public function dataProviderUploadedFilesWithMediaTypeParams(): iterable
    {
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [
                $uploadedFileResource,
                $uploadedFile->mimeType,
                $uploadedFile->mimeType,
            ];
        }

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [
                $uploadedFileResource,
                null,
                null,
            ];
        }

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [
                $uploadedFileResource,
                '',
                null,
            ];
        }
    }
}
