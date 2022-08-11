<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
};
use PHPUnit\Framework\Attributes;

use function rand;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFile::class)]
#[Attributes\Small]
class UploadedFileGetSizeTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithSizeParams')]
    public function getSize($resource, int|null $size, int|null $sizeExpected): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, $size);
        $sizeCaught     = $uploadedFile->getSize();

        static::assertSame($sizeExpected, $sizeCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function getSizeEmpty($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        static::assertNull($uploadedFile->getSize());
    }

    public function dataProviderUploadedFileWithSizeParams(): iterable
    {
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [
                $uploadedFileResource,
                $uploadedFile->size,
                $uploadedFile->size,
            ];
        }

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);
            $fileSize               = rand(1, 999999);

            yield [
                $uploadedFileResource,
                $fileSize,
                $fileSize,
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
                0,
                null,
            ];
        }
    }
}
