<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFileError,
    UploadedFile\UploadedFileFactory,
};
use InvalidArgumentException;
use PHPUnit\Framework\Attributes;

use function md5_file;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFileFactory::class)]
#[Attributes\Small]
class UploadedFileFactoryTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesInvalid')]
    public function constructorThrowsException($resource): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream = new Stream($resource);
        (new UploadedFileFactory())->createUploadedFile($stream);

        static::fail();
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function uploadedFileStream($resource): void
    {
        $stream             = new Stream($resource);
        $streamFilePath     = $stream->getMetadata('uri');
        $streamFileHash     = md5_file($streamFilePath);

        $uploadedFile       = (new UploadedFileFactory())->createUploadedFile($stream);
        $uploadedFilePath   = $uploadedFile->getStream()->getMetadata('uri');
        $uploadedFileHash   = md5_file($uploadedFilePath);

        static::assertSame($streamFilePath, $uploadedFilePath);
        static::assertSame($streamFileHash, $uploadedFileHash);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProviderExternal(
        UploadedFileGetSizeTest::class,
        'dataProviderUploadedFileWithSizeParams'
    )]
    public function uploadedFileSize(
        $resource,
        int|null $size,
        int|null $sizeExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile($stream, $size);
        $sizeCaught     = $uploadedFile->getSize();

        static::assertSame($sizeExpected, $sizeCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProviderExternal(
        UploadedFileGetErrorTest::class,
        'dataProviderUploadedFileWithErrorParams'
    )]
    public function uploadedFileError(
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

        static::assertSame($errorExpected, $errorCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProviderExternal(
        UploadedFileGetClientNameTest::class,
        'dataProviderUploadedFilesWithNameParams'
    )]
    public function uploadedFileClientFilename(
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

        static::assertSame($fileNameExpected, $fileNameCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProviderExternal(
        UploadedFileGetClientMediaTypeTest::class,
        'dataProviderUploadedFilesWithMediaTypeParams'
    )]
    public function uploadedFileClientMediaType(
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

        static::assertSame($mediaTypeExpected, $mediaTypeCaught);
    }
}
