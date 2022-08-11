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
class UploadedFileGetClientNameTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFilesWithNameParams')]
    public function getClientFilename(
        $resource,
        string|null $fileName,
        string|null $fileNameExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile(
            $stream,
            null,
            UploadedFileError::OK,
            $fileName
        );
        $fileNameCaught = $uploadedFile->getClientFilename();

        static::assertSame($fileNameExpected, $fileNameCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function getClientFilenameEmpty($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        static::assertNull($uploadedFile->getClientFilename());
    }

    public function dataProviderUploadedFilesWithNameParams(): iterable
    {
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [
                $uploadedFileResource,
                $uploadedFile->name,
                $uploadedFile->name,
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
