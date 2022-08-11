<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile,
};
use InvalidArgumentException;
use PHPUnit\Framework\Attributes;

/**
 * @internal
 */
#[Attributes\CoversClass(UploadedFile::class)]
#[Attributes\Small]
class UploadedFileConstructorTest extends AbstractUploadedFileTestCase
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
        new UploadedFile($stream);

        static::fail();
    }
}
