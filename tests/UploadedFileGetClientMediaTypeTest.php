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
 * Testing metadata reading process.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileGetClientMediaTypeTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::getClientMediaType" provides expected value.
     *
     * @covers          UploadedFile::getClientMediaType
     * @dataProvider    dataProviderUploadedFilesWithMediaTypeParams
     *
     * @param           resource    $resource           Resource.
     * @param           string|null $mediaType          Media type.
     * @param           string|null $mediaTypeExpected  Media type expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetValue(
                    $resource,
        string|null $mediaType,
        string|null $mediaTypeExpected
    ): void {
        $stream             = new Stream($resource);
        $uploadedFile       = new UploadedFile($stream, null, null, null, $mediaType);
        $mediaTypeCaught    = $uploadedFile->getClientMediaType();

        self::assertEquals(
            $mediaTypeExpected,
            $mediaTypeCaught,
            "Action \"UploadedFile->getClientMediaType\" returned unexpected result.\n".
            "Action was called with parameters (client media type => $mediaType).\n".
            "Expected result is \"$mediaTypeExpected\".\n".
            "Caught result is \"$mediaTypeCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: uploaded files with client media type values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFilesWithMediaTypeParams(): array
    {
        $result = [];

        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $fileType               = $uploadedFile['type'];
            $result[]               = [$uploadedFileResource, $fileType, $fileType];
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