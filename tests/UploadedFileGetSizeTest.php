<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
};

use function rand;

/**
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing size info providing process.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileGetSizeTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::getSize
     * @dataProvider    dataProviderUploadedFileWithSizeParams
     *
     * @param resource $resource
     */
    public function testGetValue($resource, int|null $size, int|null $sizeExpected): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, $size);
        $sizeCaught     = $uploadedFile->getSize();

        static::assertSame(
            $sizeExpected,
            $sizeCaught,
            "Action [UploadedFile->getSize] returned unexpected result.\n".
            "Constructor was called with parameters [size => {$size}].\n".
            "Expected result is [{$sizeExpected}].\n".
            "Caught result is [{$sizeCaught}]."
        );
    }

    /**
     * @covers          UploadedFile::getSize
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource
     */
    public function testGetEmptyValue($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        static::assertNull(
            $uploadedFile->getSize(),
            "Action [UploadedFile->getSize] returned unexpected result.\n".
            "Action was called without parameters [size].\n".
            "Expected result is null.\n".
            'Caught result is not null.'
        );
    }

    /**
     * Data provider: uploaded files with size values.
     */
    public function dataProviderUploadedFileWithSizeParams(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [
                $uploadedFileResource,
                $uploadedFile->getSize(),
                $uploadedFile->getSize(),
            ];
        }
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $fileSize               = rand(1, 999999);
            $result[]               = [
                $uploadedFileResource,
                $fileSize,
                $fileSize,
            ];
        }
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [
                $uploadedFileResource,
                null,
                null,
            ];
        }
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [
                $uploadedFileResource,
                0,
                null,
            ];
        }

        return $result;
    }
}
