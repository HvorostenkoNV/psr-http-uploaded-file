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
 * Testing file client name info providing process.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileGetClientNameTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::getClientFilename
     * @dataProvider    dataProviderUploadedFilesWithNameParams
     *
     * @param resource $resource
     */
    public function testGetValue(
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

        static::assertSame(
            $fileNameExpected,
            $fileNameCaught,
            "Action [UploadedFile->getClientFilename] returned unexpected result.\n".
            "Action was called with parameters [client file name => {$fileName}].\n".
            "Expected result is [{$fileNameExpected}].\n".
            "Caught result is [{$fileNameCaught}]."
        );
    }

    /**
     * @covers          UploadedFile::getClientFilename
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource
     */
    public function testGetEmptyValue($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        static::assertNull(
            $uploadedFile->getClientFilename(),
            "Action [UploadedFile->getClientFilename] returned unexpected result.\n".
            "Action was called without parameters [client file name].\n".
            "Expected result is null.\n".
            'Caught result is not null.'
        );
    }

    /**
     * Data provider: uploaded files with client file name values.
     */
    public function dataProviderUploadedFilesWithNameParams(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [
                $uploadedFileResource,
                $uploadedFile->getName(),
                $uploadedFile->getName(),
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
