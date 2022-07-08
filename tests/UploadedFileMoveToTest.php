<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\Stream\Stream;
use HNV\Http\UploadedFile\{
    UploadedFile,
    UploadedFileError,
};
use InvalidArgumentException;
use RuntimeException;

use function array_filter;
use function array_merge;
use function array_shift;
use function count;
use function md5_file;
use function rmdir;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing upload file moving process.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileMoveToTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsValid
     *
     * @param resource $resource
     */
    public function testRunProcess($resource, string $fileNewPath): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);
        $fileOldPath    = $stream->getMetadata('uri');
        $fileOldHash    = md5_file($fileOldPath);

        $uploadedFile->moveTo($fileNewPath);

        static::assertFileDoesNotExist(
            $fileOldPath,
            "Action [UploadedFile->moveTo] returned unexpected result.\n".
            "Action was called with parameters [new path => {$fileNewPath}].\n".
            "Expected result is [file will be not exists by old path {$fileOldPath}].\n".
            'Caught result is "file is still exist".'
        );
        static::assertFileExists(
            $fileNewPath,
            "Action [UploadedFile->moveTo] returned unexpected result.\n".
            "Action was called with parameters [new path => {$fileNewPath}].\n".
            "Expected result is [file will be exists by new path].\n".
            'Caught result is "file is still exist".'
        );
        static::assertSame(
            $fileOldHash,
            md5_file($fileNewPath),
            "Action [UploadedFile->moveTo] returned unexpected result.\n".
            "Action was called with parameters (new path => {$fileNewPath}).\n".
            "Expected result is [replaced file will has the same hash].\n".
            'Caught result is "file hash is not the same".'
        );
    }

    /**
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsInvalid
     *
     * @param resource $resource
     */
    public function testRunProcessThrowsExceptionWithInvalidPath(
        $resource,
        string $fileNewPath
    ): void {
        $this->expectException(InvalidArgumentException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        $uploadedFile->moveTo($fileNewPath);

        static::fail(
            "Action [UploadedFile->moveTo] threw no expected exception.\n".
            "Action was called with parameters (new path => invalid path for replacing).\n".
            "Expects [InvalidArgumentException] exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsValid
     *
     * @param resource $resource
     */
    public function testRunProcessThrowsExceptionWithUnreachableFile(
        $resource,
        string $fileNewPath
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $fileOldPath    = $stream->getMetadata('uri');
        $uploadedFile   = new UploadedFile($stream);

        unlink($fileOldPath);
        $uploadedFile->moveTo($fileNewPath);

        static::fail(
            "Action [UploadedFile->moveTo] threw no expected exception.\n".
            "Underlying file was previously deleted\n".
            "Expects [RuntimeException] exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsValid
     *
     * @param resource $resource
     */
    public function testRunProcessThrowsExceptionOnCallingTwice(
        $resource,
        string $fileNewPath
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        $uploadedFile->moveTo($fileNewPath);
        $uploadedFile->moveTo($fileNewPath);

        static::fail(
            "Action [UploadedFile->moveTo->moveTo] threw no expected exception.\n".
            "Expects [RuntimeException] exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithCriticalError
     *
     * @param resource $resource
     */
    public function testRunProcessThrowsExceptionWithFileError(
        $resource,
        string $fileNewPath,
        UploadedFileError $error
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, $error);

        $uploadedFile->moveTo($fileNewPath);

        static::fail(
            "Action [UploadedFile->moveTo] threw no expected exception.\n".
            "Action was called with parameters (file error => {$error->value()}).\n".
            "Expects [RuntimeException] exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: uploaded files resources with replacing valid values.
     */
    public function dataProviderUploadedFileWithReplacingParamsValid(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $temporaryFile          = $this->generateFile();
            $result[]               = [$uploadedFileResource, $temporaryFile];
        }
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $temporaryFile          = $this->generateFile();
            $result[]               = [$uploadedFileResource, $temporaryFile];
            unlink($temporaryFile);
        }

        return $result;
    }

    /**
     * Data provider: uploaded files resources with replacing invalid values.
     */
    public function dataProviderUploadedFileWithReplacingParamsInvalid(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $temporaryDirectory     = $this->generateDirectory();
            $filePath               = $temporaryDirectory.DIRECTORY_SEPARATOR.'someFile';
            $result[]               = [$uploadedFileResource, $filePath];
            rmdir($temporaryDirectory);
        }

        return $result;
    }

    /**
     * Data provider: uploaded file error critical values.
     */
    public function dataProviderUploadedFileWithCriticalError(): array
    {
        $errorsCritical     = array_filter(
            UploadedFileError::cases(),
            fn (UploadedFileError $case): bool => $case !== UploadedFileError::OK
        );
        $validReplacingData = [];
        $result             = [];

        while (count($validReplacingData) < count($errorsCritical)) {
            $validReplacingData = array_merge(
                $validReplacingData,
                $this->dataProviderUploadedFileWithReplacingParamsValid()
            );
        }

        foreach ($errorsCritical as $error) {
            $data                   = array_shift($validReplacingData);
            $uploadedFileResource   = $data[0];
            $pathForReplace         = $data[1];
            $result[]               = [$uploadedFileResource, $pathForReplace, $error];
        }

        return $result;
    }
}
