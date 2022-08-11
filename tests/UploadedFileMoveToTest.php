<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\Stream\Stream;
use HNV\Http\UploadedFile\{
    UploadedFile,
    UploadedFileError,
};
use InvalidArgumentException;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function array_filter;
use function array_shift;
use function count;
use function md5_file;
use function rmdir;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFile::class)]
#[Attributes\Small]
class UploadedFileMoveToTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithReplacingParamsValid')]
    public function moveTo($resource, string $fileNewPath): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);
        $fileOldPath    = $stream->getMetadata('uri');
        $fileOldHash    = md5_file($fileOldPath);

        $uploadedFile->moveTo($fileNewPath);

        static::assertFileDoesNotExist($fileOldPath);
        static::assertFileExists($fileNewPath);
        static::assertSame($fileOldHash, md5_file($fileNewPath));
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithReplacingParamsInvalid')]
    public function moveToThrowsExceptionWithInvalidPath($resource, string $fileNewPath): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        $uploadedFile->moveTo($fileNewPath);

        static::fail();
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithReplacingParamsValid')]
    public function moveToThrowsExceptionWithUnreachableFile(
        $resource,
        string $fileNewPath
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $fileOldPath    = $stream->getMetadata('uri');
        $uploadedFile   = new UploadedFile($stream);

        unlink($fileOldPath);
        $uploadedFile->moveTo($fileNewPath);

        static::fail();
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithReplacingParamsValid')]
    public function moveToThrowsExceptionOnCallingTwice(
        $resource,
        string $fileNewPath
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        $uploadedFile->moveTo($fileNewPath);
        $uploadedFile->moveTo($fileNewPath);

        static::fail();
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderUploadedFileWithCriticalError')]
    public function moveToThrowsExceptionWithFileError(
        $resource,
        string $fileNewPath,
        UploadedFileError $error
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, $error);

        $uploadedFile->moveTo($fileNewPath);

        static::fail();
    }

    public function dataProviderUploadedFileWithReplacingParamsValid(): iterable
    {
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);
            $temporaryFile          = $this->generateFile();

            yield [$uploadedFileResource, $temporaryFile];
        }

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);
            $temporaryFile          = $this->generateFile();

            unlink($temporaryFile);
            yield [$uploadedFileResource, $temporaryFile];
        }
    }

    public function dataProviderUploadedFileWithReplacingParamsInvalid(): iterable
    {
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);
            $temporaryDirectory     = $this->generateDirectory();
            $filePath               = $temporaryDirectory.DIRECTORY_SEPARATOR.'someFile';

            rmdir($temporaryDirectory);
            yield [$uploadedFileResource, $filePath];
        }
    }

    public function dataProviderUploadedFileWithCriticalError(): iterable
    {
        $errorsCritical     = array_filter(
            UploadedFileError::cases(),
            fn (UploadedFileError $case): bool => $case !== UploadedFileError::OK
        );
        $validReplacingData = [];

        while (count($validReplacingData) < count($errorsCritical)) {
            foreach ($this->dataProviderUploadedFileWithReplacingParamsValid() as $data) {
                $validReplacingData[] = $data;
            }
        }

        foreach ($errorsCritical as $error) {
            $data                   = array_shift($validReplacingData);
            $uploadedFileResource   = $data[0];
            $pathForReplace         = $data[1];

            yield [$uploadedFileResource, $pathForReplace, $error];
        }
    }
}
