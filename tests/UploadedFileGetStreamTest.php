<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
};
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function md5_file;
use function unlink;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFile::class)]
#[Attributes\Small]
class UploadedFileGetStreamTest extends AbstractUploadedFileTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function getStream($resource): void
    {
        $stream             = new Stream($resource);
        $streamFilePath     = $stream->getMetadata('uri');
        $streamFileHash     = md5_file($streamFilePath);

        $uploadedFile       = new UploadedFile($stream);
        $uploadedFilePath   = $uploadedFile->getStream()->getMetadata('uri');
        $uploadedFileHash   = md5_file($uploadedFilePath);

        static::assertSame($streamFilePath, $uploadedFilePath);
        static::assertSame($streamFileHash, $uploadedFileHash);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesValid')]
    public function getStreamThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $filePath       = $stream->getMetadata('uri');
        $uploadedFile   = new UploadedFile($stream);

        unlink($filePath);
        $uploadedFile->getStream();

        static::fail();
    }
}
