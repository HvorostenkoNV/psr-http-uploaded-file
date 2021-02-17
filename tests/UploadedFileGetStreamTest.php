<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Resource                              as ResourceGenerator;
use HNV\Http\UploadedFileTests\Generator\UploadedFile               as UploadedFileGenerator;
use HNV\Http\UploadedFileTests\Collection\ResourceAccessMode\Valid  as ResourceAccessModeValid;
use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile
};

use function md5_file;
use function unlink;
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing stream providing process.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileGetStreamTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::getStream" provides expected stream object.
     *
     * @covers          UploadedFile::getStream
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetValue($resource): void
    {
        $stream             = new Stream($resource);
        $streamFilePath     = $stream->getMetadata('uri');
        $streamFileHash     = md5_file($streamFilePath);

        $uploadedFile       = new UploadedFile($stream);
        $uploadedFilePath   = $uploadedFile->getStream()->getMetadata('uri');
        $uploadedFileHash   = md5_file($uploadedFilePath);

        self::assertEquals(
            $streamFilePath,
            $uploadedFilePath,
            "Action \"UploadedFile->getStream\" returned unexpected result.\n".
            "Expected result is \"stream file path => $streamFilePath\".\n".
            "Caught result is \"stream file path => $uploadedFilePath\"."
        );
        self::assertEquals(
            $streamFileHash,
            $uploadedFileHash,
            "Action \"UploadedFile->getStream\" returned unexpected result.\n".
            "Expected result is \"stream file hash as was set\".\n".
            "Caught result is \"stream file hash is not the same\"."
        );
    }
    /** **********************************************************************
     * Test "UploadedFile::getStream" throws exception with no stream available.
     *
     * @covers          UploadedFile::getStream
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetValueThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $filePath       = $stream->getMetadata('uri');
        $uploadedFile   = new UploadedFile($stream);

        unlink($filePath);
        $uploadedFile->getStream();

        self::fail(
            "Action \"UploadedFile->getStream\" threw no expected exception.\n".
            "Underlying file was deleted.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Data provider: uploaded files resources.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResources(): array
    {
        $result = [];

        foreach (ResourceAccessModeValid::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $result[]               = [$uploadedFileResource];
        }

        return $result;
    }
}