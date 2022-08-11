<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\Stream\Stream;
use HNV\Http\UploadedFile\{
    UploadedFile,
    UploadedFileError,
};
use PHPUnit\Framework\Attributes;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFile::class)]
#[Attributes\Small]
class UploadedFileGetErrorTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithErrorParams')]
    public function getError(
        $resource,
        UploadedFileError $error,
        int $errorExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, $error);
        $errorCaught    = $uploadedFile->getError();

        static::assertSame($errorExpected, $errorCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function getErrorEmpty($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);
        $errorDefault   = UploadedFileError::OK->value();
        $errorCaught    = $uploadedFile->getError();

        static::assertSame($errorDefault, $errorCaught);
    }

    public function dataProviderUploadedFileWithErrorParams(): iterable
    {
        foreach (UploadedFileError::cases() as $error) {
            foreach ($this->getResourceAccessModesValid() as $mode) {
                $uploadedFile           = $this->generateUploadedFile();
                $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

                yield [
                    $uploadedFileResource,
                    $error,
                    $error->value(),
                ];
            }
        }

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [
                $uploadedFileResource,
                $uploadedFile->error,
                $uploadedFile->error->value(),
            ];
        }
    }
}
