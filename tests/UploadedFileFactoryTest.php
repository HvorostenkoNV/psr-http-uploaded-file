<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFileError,
    UploadedFile\UploadedFileFactory,
};
use InvalidArgumentException;

use function md5_file;

/**
 * PSR-7 UploadedFileFactoryInterface implementation test.
 *
 * @internal
 * @covers UploadedFileFactory
 * @small
 */
class UploadedFileFactoryTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderResourcesInvalid
     *
     * @param resource $resource
     */
    public function testConstructorThrowsException($resource): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream = new Stream($resource);
        (new UploadedFileFactory())->createUploadedFile($stream);

        static::fail(
            "Action [UploadedFileFactory->createUploadedFile] threw no expected exception.\n".
            "Action was called with parameters [stream => invalid stream].\n".
            "Expects [InvalidArgumentException] exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource
     */
    public function testUploadedFileStream($resource): void
    {
        $stream             = new Stream($resource);
        $streamFilePath     = $stream->getMetadata('uri');
        $streamFileHash     = md5_file($streamFilePath);

        $uploadedFile       = (new UploadedFileFactory())->createUploadedFile($stream);
        $uploadedFilePath   = $uploadedFile->getStream()->getMetadata('uri');
        $uploadedFileHash   = md5_file($uploadedFilePath);

        static::assertSame(
            $streamFilePath,
            $uploadedFilePath,
            "Action [UploadedFile->getStream] returned unexpected result.\n".
            "Expected result is [stream file path => {$streamFilePath}].\n".
            "Caught result is [stream file path => {$uploadedFilePath}]."
        );
        static::assertSame(
            $streamFileHash,
            $uploadedFileHash,
            "Action [UploadedFile->getStream] returned unexpected result.\n".
            "Expected result is [stream file hash as was set].\n".
            'Caught result is "stream file hash is not the same".'
        );
    }

    /**
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    \HNV\Http\UploadedFileTests\UploadedFileGetSizeTest::dataProviderUploadedFileWithSizeParams
     *
     * @param resource $resource
     */
    public function testUploadedFileSize(
        $resource,
        int|null $size,
        int|null $sizeExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile($stream, $size);
        $sizeCaught     = $uploadedFile->getSize();

        static::assertSame(
            $sizeExpected,
            $sizeCaught,
            "Action [UploadedFileFactory->createUploadedFile->getSize] returned unexpected result.\n".
            "Action was called with parameters [size => {$size}].\n".
            "Expected result is [{$sizeExpected}].\n".
            "Caught result is [{$sizeCaught}]."
        );
    }

    /**
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    \HNV\Http\UploadedFileTests\UploadedFileGetErrorTest::dataProviderUploadedFileWithErrorParams
     *
     * @param resource $resource
     */
    public function testUploadedFileError(
        $resource,
        UploadedFileError $error,
        int $errorExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile(
            $stream,
            null,
            $error->value()
        );
        $errorCaught    = $uploadedFile->getError();

        static::assertSame(
            $errorExpected,
            $errorCaught,
            "Action [UploadedFileFactory->createUploadedFile->getError] returned unexpected result.\n".
            "Action was called with parameters [error => {$error->value()}].\n".
            "Expected result is [{$errorExpected}].\n".
            "Caught result is [{$errorCaught}]."
        );
    }

    /**
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    \HNV\Http\UploadedFileTests\UploadedFileGetClientNameTest::dataProviderUploadedFilesWithNameParams
     *
     * @param resource $resource
     */
    public function testUploadedFileClientFilename(
        $resource,
        string|null $fileName,
        string|null $fileNameExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile(
            $stream,
            null,
            0,
            $fileName
        );
        $fileNameCaught = $uploadedFile->getClientFilename();

        static::assertSame(
            $fileNameExpected,
            $fileNameCaught,
            'Action "UploadedFileFactory->createUploadedFile->getClientFilename"'.
            " returned unexpected result.\n".
            "Action was called with parameters [client file name => {$fileName}].\n".
            "Expected result is [{$fileNameExpected}].\n".
            "Caught result is [{$fileNameCaught}]."
        );
    }

    /**
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    \HNV\Http\UploadedFileTests\UploadedFileGetClientMediaTypeTest::dataProviderUploadedFilesWithMediaTypeParams
     *
     * @param resource $resource
     */
    public function testUploadedFileClientMediaType(
        $resource,
        string|null $mediaType,
        string|null $mediaTypeExpected
    ): void {
        $stream             = new Stream($resource);
        $uploadedFile       = (new UploadedFileFactory())->createUploadedFile(
            $stream,
            null,
            0,
            null,
            $mediaType
        );
        $mediaTypeCaught    = $uploadedFile->getClientMediaType();

        static::assertSame(
            $mediaTypeExpected,
            $mediaTypeCaught,
            'Action "UploadedFileFactory->createUploadedFile->getClientFilename"'.
            " returned unexpected result.\n".
            "Action was called with parameters [client file name => {$mediaType}].\n".
            "Expected result is [{$mediaTypeExpected}].\n".
            "Caught result is [{$mediaTypeCaught}]."
        );
    }
}
