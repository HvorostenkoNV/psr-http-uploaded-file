<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use Throwable;
use InvalidArgumentException;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\{
    File        as FileGenerator,
    Directory   as DirectoryGenerator,
    Resource    as ResourceGenerator
};
use HNV\Http\UploadedFileTests\Generator\UploadedFile               as UploadedFileGenerator;
use HNV\Http\UploadedFileTests\Collection\ResourceAccessMode\Valid  as ResourceAccessModeValid;
use HNV\Http\Stream\Stream;
use HNV\Http\UploadedFile\{
    UploadedFile,
    Collection\UploadedFileError as UploadedFileErrorCollection
};

use function count;
use function array_keys;
use function array_diff;
use function array_merge;
use function array_shift;
use function file_exists;
use function md5_file;
use function unlink;
use function rmdir;

use const DIRECTORY_SEPARATOR;
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing upload file moving process.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileMoveToTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::moveTo" replace file.
     *
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsValid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $fileNewPath        File new path.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcess($resource, string $fileNewPath): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);
        $fileOldPath    = $stream->getMetadata('uri');
        $fileOldHash    = md5_file($fileOldPath);

        $uploadedFile->moveTo($fileNewPath);

        self::assertFalse(
            file_exists($fileOldPath),
            "Action \"UploadedFile->moveTo\" returned unexpected result.\n".
            "Action was called with parameters (new path => $fileNewPath).\n".
            "Expected result is \"file will be not exists by old path $fileOldPath\".\n".
            "Caught result is \"file is still exist\"."
        );
        self::assertTrue(
            file_exists($fileNewPath),
            "Action \"UploadedFile->moveTo\" returned unexpected result.\n".
            "Action was called with parameters (new path => $fileNewPath).\n".
            "Expected result is \"file will be exists by new path\".\n".
            "Caught result is \"file is still exist\"."
        );
        self::assertEquals(
            $fileOldHash,
            md5_file($fileNewPath),
            "Action \"UploadedFile->moveTo\" returned unexpected result.\n".
            "Action was called with parameters (new path => $fileNewPath).\n".
            "Expected result is \"replaced file will has the same hash\".\n".
            "Caught result is \"file hash is not the same\"."
        );
    }
    /** **********************************************************************
     * Test "UploadedFile::moveTo" throws exception with invalid path for replacing.
     *
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsInvalid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $fileNewPath        File new path.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcessThrowsExceptionWithInvalidPath($resource, string $fileNewPath): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        $uploadedFile->moveTo($fileNewPath);

        self::fail(
            "Action \"UploadedFile->moveTo\" threw no expected exception.\n".
            "Action was called with parameters (new path => invalid path for replacing).\n".
            "Expects \"InvalidArgumentException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "UploadedFile::moveTo" throws exception with unreachable file.
     *
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsValid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $fileNewPath        File new path.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcessThrowsExceptionWithUnreachableFile($resource, string $fileNewPath): void
    {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $fileOldPath    = $stream->getMetadata('uri');
        $uploadedFile   = new UploadedFile($stream);

        unlink($fileOldPath);
        $uploadedFile->moveTo($fileNewPath);

        self::fail(
            "Action \"UploadedFile->moveTo\" threw no expected exception.\n".
            "Underlying file was previously deleted\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "UploadedFile::moveTo" throws exception on calling method twice.
     *
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithReplacingParamsValid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $fileNewPath        File new path.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcessThrowsExceptionOnCallingTwice($resource, string $fileNewPath): void
    {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream);

        $uploadedFile->moveTo($fileNewPath);
        $uploadedFile->moveTo($fileNewPath);

        self::fail(
            "Action \"UploadedFile->moveTo->moveTo\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "UploadedFile::moveTo" throws exception with any uploaded file error.
     *
     * @covers          UploadedFile::moveTo
     * @dataProvider    dataProviderUploadedFileWithCriticalError
     *
     * @param           resource    $resource           Resource.
     * @param           string      $fileNewPath        File new path.
     * @param           int         $error              Uploaded file error.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcessThrowsExceptionWithFileError(
        $resource,
        string  $fileNewPath,
        int     $error
    ): void {
        $this->expectException(RuntimeException::class);

        $stream         = new Stream($resource);
        $uploadedFile   = new UploadedFile($stream, null, $error);

        $uploadedFile->moveTo($fileNewPath);

        self::fail(
            "Action \"UploadedFile->moveTo\" threw no expected exception.\n".
            "Action was called with parameters (file error => $error).\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Data provider: uploaded files resources with replacing valid values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithReplacingParamsValid(): array
    {
        $result = [];

        foreach (ResourceAccessModeValid::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $temporaryFile          = (new FileGenerator())->generate();
            $result[]               = [$uploadedFileResource, $temporaryFile];
        }
        foreach (ResourceAccessModeValid::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $temporaryFile          = (new FileGenerator())->generate();
            $result[]               = [$uploadedFileResource, $temporaryFile];
            unlink($temporaryFile);
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: uploaded files resources with replacing invalid values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithReplacingParamsInvalid(): array
    {
        $result = [];

        foreach (ResourceAccessModeValid::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $temporaryDirectory     = (new DirectoryGenerator())->generate();
            $filePath               = $temporaryDirectory.DIRECTORY_SEPARATOR.'someFile';
            $result[]               = [$uploadedFileResource, $filePath];
            rmdir($temporaryDirectory);
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: uploaded file error critical values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithCriticalError(): array
    {
        $errorsAll          = array_keys(UploadedFileErrorCollection::get());
        $errorsOk           = [UploadedFileErrorCollection::STATUS_OK];
        $errorsCritical     = array_diff($errorsAll, $errorsOk);
        $validReplacingData = [];
        $result             = [];

        while (count($validReplacingData) < count($errorsCritical)) {
            $validReplacingData = array_merge(
                $validReplacingData,
                $this->dataProviderUploadedFileWithReplacingParamsValid()
            );
        }

        foreach ($errorsCritical as $error) {
            $data                   = array_shift($validReplacingData);
            $uploadedFileResource   = $data[0];
            $pathForReplace         = $data[1];
            $result[]               = [$uploadedFileResource, $pathForReplace, $error];
        }

        return $result;
    }
}