<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
};
use RuntimeException;

use function md5_file;
use function unlink;

/**
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing stream providing process.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileGetStreamTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::getStream
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource
     */
    public function testGetValue($resource): void
    {
        $stream             = new Stream($resource);
        $streamFilePath     = $stream->getMetadata('uri');
        $streamFileHash     = md5_file($streamFilePath);

        $uploadedFile       = new UploadedFile($stream);
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
     * @covers          UploadedFile::getStream
     * @dataProvider    dataProviderResourcesValid
     *
     * @param resource $resource resource
     */
    public function testGetValueThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $filePath       = $stream->getMetadata('uri');
        $uploadedFile   = new UploadedFile($stream);

        unlink($filePath);
        $uploadedFile->getStream();

        static::fail(
            "Action [UploadedFile->getStream] threw no expected exception.\n".
            "Underlying file was deleted.\n".
            "Expects [RuntimeException] exception.\n".
            'Caught no exception.'
        );
    }
}
