<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Resource                              as ResourceGenerator;
use HNV\Http\UploadedFileTests\Generator\UploadedFile               as UploadedFileGenerator;
use HNV\Http\UploadedFileTests\Collection\ResourceAccessMode\Valid  as ResourceAccessModeValid;
use HNV\Http\Stream\Stream;
use HNV\Http\UploadedFile\{
    UploadedFile,
    Collection\UploadedFileError as UploadedFileErrorCollection
};

use function in_array;
use function array_keys;
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing error info providing process.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileGetErrorTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::getError" provides expected value.
     *
     * @covers          UploadedFile::getError
     * @dataProvider    dataProviderUploadedFileWithErrorParams
     *
     * @param           resource    $resource           Resource.
     * @param           int|null    $error              Error.
     * @param           int         $errorExpected      Error expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetValue($resource, int|null $error, int $errorExpected): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, $errorExpected);
        $errorCaught    = $uploadedFile->getError();

        self::assertEquals(
            $errorExpected,
            $errorCaught,
            "Action \"UploadedFile->getError\" returned unexpected result.\n".
            "Action was called with parameters (error => $error).\n".
            "Expected result is \"$errorExpected\".\n".
            "Caught result is \"$errorCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: uploaded files with errors values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithErrorParams(): array
    {
        $errorsValuesValid      = array_keys(UploadedFileErrorCollection::get());
        $errorsValuesInvalid    = [];
        $errorOk                = UploadedFileErrorCollection::STATUS_OK;
        $result                 = [];

        for ($iterator = -100; $iterator < 100; $iterator++) {
            if (!in_array($iterator, $errorsValuesValid)) {
                $errorsValuesInvalid[] = $iterator;
            }
        }

        foreach ($errorsValuesValid as $error) {
            foreach (ResourceAccessModeValid::get() as $mode) {
                $uploadedFile           = (new UploadedFileGenerator())->generate();
                $uploadedFileResource   = (new ResourceGenerator(
                    $uploadedFile['tmp_name'],
                    $mode)
                )->generate();
                $result[]               = [$uploadedFileResource, $error, $error];
            }
        }
        foreach ($errorsValuesInvalid as $error) {
            foreach (ResourceAccessModeValid::get() as $mode) {
                $uploadedFile           = (new UploadedFileGenerator())->generate();
                $uploadedFileResource   = (new ResourceGenerator(
                    $uploadedFile['tmp_name'],
                    $mode)
                )->generate();
                $result[]               = [$uploadedFileResource, $error, $errorOk];
            }
        }

        foreach (ResourceAccessModeValid::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $fileError              = $uploadedFile['error'];
            $result[]               = [$uploadedFileResource, $fileError, $fileError];
        }
        foreach (ResourceAccessModeValid::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $result[]               = [$uploadedFileResource, null, $errorOk];
        }

        return $result;
    }
}