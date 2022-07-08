<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\Stream\Stream;
use HNV\Http\UploadedFile\{
    UploadedFile,
    UploadedFileError,
};

/**
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing error info providing process.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileGetErrorTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::getError
     * @dataProvider    dataProviderUploadedFileWithErrorParams
     *
     * @param resource $resource
     */
    public function testGetValue(
        $resource,
        UploadedFileError $error,
        int $errorExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, $error);
        $errorCaught    = $uploadedFile->getError();

        static::assertSame(
            $errorExpected,
            $errorCaught,
            "Action [UploadedFile->getError] returned unexpected result.\n".
            "Action was called with parameters [error => {$error->value()}].\n".
            "Expected result is [{$errorExpected}].\n".
            "Caught result is [{$errorCaught}]."
        );
    }

    /**
     * @covers          UploadedFile::getError
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource
     */
    public function testGetEmptyValue($resource): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);
        $errorDefault   = UploadedFileError::OK->value();
        $errorCaught    = $uploadedFile->getError();

        static::assertSame(
            $errorDefault,
            $errorCaught,
            "Action [UploadedFile->getError] returned unexpected result.\n".
            "Action was called without parameters [error].\n".
            "Expected result is [{$errorDefault}].\n".
            "Caught result is [{$errorCaught}]."
        );
    }

    /**
     * Data provider: uploaded files with errors values.
     */
    public function dataProviderUploadedFileWithErrorParams(): array
    {
        $result = [];

        foreach (UploadedFileError::cases() as $error) {
            foreach ($this->getResourceAccessModesValid() as $mode) {
                $uploadedFile           = $this->generateUploadedFile();
                $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
                $result[]               = [
                    $uploadedFileResource,
                    $error,
                    $error->value(),
                ];
            }
        }
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [
                $uploadedFileResource,
                $uploadedFile->getError(),
                $uploadedFile->getError()->value(),
            ];
        }

        return $result;
    }
}
