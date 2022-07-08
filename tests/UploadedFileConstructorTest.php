<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
};
use InvalidArgumentException;

/**
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing constructor behavior.
 *
 * @internal
 * @covers UploadedFile
 * @small
 */
class UploadedFileConstructorTest extends AbstractUploadedFileTest
{
    /**
     * @covers          UploadedFile::__constructor
     * @dataProvider    dataProviderResourcesInvalid
     *
     * @param resource $resource
     */
    public function testConstructorThrowsException($resource): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream = new Stream($resource);
        new UploadedFile($stream);

        static::fail(
            "Action [UploadedFile->__construct] threw no expected exception.\n".
            "Action was called with parameters [stream => invalid stream].\n".
            "Expects [InvalidArgumentException] exception.\n".
            'Caught no exception.'
        );
    }
}
