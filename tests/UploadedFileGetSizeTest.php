<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Resource                  as ResourceGenerator;
use HNV\Http\UploadedFileTests\Generator\UploadedFile   as UploadedFileGenerator;
use HNV\Http\UploadedFileTests\Collection\ResourceAccessMode\{
    ReadableAndWritable as ResourceAccessModeReadableAndWritable
};
use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile
};

use function rand;
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing size info providing process.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileGetSizeTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::getSize" provides expected value.
     *
     * @covers          UploadedFile::getSize
     * @dataProvider    dataProviderUploadedFileWithSizeParams
     *
     * @param           resource    $resource           Resource.
     * @param           int|null    $size               Size.
     * @param           int|null    $sizeExpected       Size expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetValue($resource, int|null $size, int|null $sizeExpected): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, $size);
        $sizeCaught     = $uploadedFile->getSize();

        self::assertEquals(
            $sizeExpected,
            $sizeCaught,
            "Action \"UploadedFile->getSize\" returned unexpected result.\n".
            "Constructor was called with parameters (size => $size).\n".
            "Expected result is \"$sizeExpected\".\n".
            "Caught result is \"$sizeCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: uploaded files with size values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithSizeParams(): array
    {
        $result = [];

        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $fileSize               = $uploadedFile['size'];
            $result[]               = [$uploadedFileResource, $fileSize, $fileSize];
        }
        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $fileSize               = rand(1, 999999);
            $result[]               = [$uploadedFileResource, $fileSize, $fileSize];
        }
        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $result[]               = [$uploadedFileResource, null, null];
        }
        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $result[]               = [$uploadedFileResource, 0, null];
        }

        return $result;
    }
}