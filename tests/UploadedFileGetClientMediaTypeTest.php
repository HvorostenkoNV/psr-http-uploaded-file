<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
    UploadedFile\UploadedFileError,
};

/**
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing metadata reading process.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileGetClientMediaTypeTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::getClientMediaType
     * @dataProvider    dataProviderUploadedFilesWithMediaTypeParams
     *
     * @param resource $resource
     */
    public function testGetValue(
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

        static::assertSame(
            $mediaTypeExpected,
            $mediaTypeCaught,
            "Action [UploadedFile->getClientMediaType] returned unexpected result.\n".
            "Action was called with parameters [client media type => {$mediaType}].\n".
            "Expected result is [{$mediaTypeExpected}].\n".
            "Caught result is [{$mediaTypeCaught}]."
        );
    }

    /**
     * @covers          UploadedFile::getClientMediaType
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource
     */
    public function testGetEmptyValue($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        static::assertNull(
            $uploadedFile->getClientMediaType(),
            "Action [UploadedFile->getClientMediaType] returned unexpected result.\n".
            "Action was called without parameters [client media type].\n".
            "Expected result is null.\n".
            'Caught result is not null.'
        );
    }

    /**
     * Data provider: uploaded files with client media type values.
     */
    public function dataProviderUploadedFilesWithMediaTypeParams(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [
                $uploadedFileResource,
                $uploadedFile->getMimeType(),
                $uploadedFile->getMimeType(),
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
                '',
                null,
            ];
        }

        return $result;
    }
}
