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
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing file client name info providing process.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileGetClientNameTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::getClientFilename" provides expected value.
     *
     * @covers          UploadedFile::getClientFilename
     * @dataProvider    dataProviderUploadedFilesWithNameParams
     *
     * @param           resource    $resource           Resource.
     * @param           string|null $fileName           File name.
     * @param           string|null $fileNameExpected   File name expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetValue(
                    $resource,
        string|null $fileName,
        string|null $fileNameExpected
    ): void {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, null, $fileName);
        $fileNameCaught = $uploadedFile->getClientFilename();

        self::assertEquals(
            $fileNameExpected,
            $fileNameCaught,
            "Action \"UploadedFile->getClientFilename\" returned unexpected result.\n".
            "Action was called with parameters (client file name => $fileName).\n".
            "Expected result is \"$fileNameExpected\".\n".
            "Caught result is \"$fileNameCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: uploaded files with client file name values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFilesWithNameParams(): array
    {
        $result = [];

        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $fileName               = $uploadedFile['name'];
            $result[]               = [$uploadedFileResource, $fileName, $fileName];
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
            $result[]               = [$uploadedFileResource, '', null];
        }

        return $result;
    }
}