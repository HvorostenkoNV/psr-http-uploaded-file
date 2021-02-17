<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use Throwable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFileFactory
};

use function md5_file;
/** ***********************************************************************************************
 * PSR-7 UploadedFileFactoryInterface implementation test.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileFactoryTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFileFactoryInterface::createUploadedFile" throws exception with invalid stream.
     *
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderUploadedFilesResourcesInvalid
     *
     * @param           resource $resource          Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testConstructorThrowsException($resource): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream = new Stream($resource);
        (new UploadedFileFactory())->createUploadedFile($stream);

        self::fail(
            "Action \"UploadedFileFactory->createUploadedFile\" threw no expected exception.\n".
            "Action was called with parameters (stream => invalid stream).\n".
            "Expects \"InvalidArgumentException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "UploadedFileFactoryInterface::createUploadedFile" provides uploaded file
     * with stream in expected state.
     *
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderUploadedFilesResourcesValid
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testUploadedFileStream($resource): void
    {
        $stream             = new Stream($resource);
        $streamFilePath     = $stream->getMetadata('uri');
        $streamFileHash     = md5_file($streamFilePath);

        $uploadedFile       = (new UploadedFileFactory())->createUploadedFile($stream);
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
     * Test "UploadedFileFactoryInterface::createUploadedFile" provides uploaded file
     * with expected size.
     *
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderUploadedFileWithSizeParams
     *
     * @param           resource    $resource           Resource.
     * @param           int|null    $size               Size.
     * @param           int|null    $sizeExpected       Size expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testUploadedFileSize($resource, int|null $size, int|null $sizeExpected): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile($stream, $size);
        $sizeCaught     = $uploadedFile->getSize();

        self::assertEquals(
            $sizeExpected,
            $sizeCaught,
            "Action \"UploadedFileFactory->createUploadedFile->getSize\" returned unexpected result.\n".
            "Action was called with parameters (size => $size).\n".
            "Expected result is \"$sizeExpected\".\n".
            "Caught result is \"$sizeCaught\"."
        );
    }
    /** **********************************************************************
     * Test "UploadedFileFactoryInterface::createUploadedFile" provides uploaded file
     * with expected error.
     *
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderUploadedFileWithErrorParams
     *
     * @param           resource    $resource           Resource.
     * @param           int|null    $error              Error.
     * @param           int         $errorExpected      Error expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testUploadedFileError($resource, int|null $error, int $errorExpected): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile($stream, null, $error);
        $errorCaught    = $uploadedFile->getError();

        self::assertEquals(
            $errorExpected,
            $errorCaught,
            "Action \"UploadedFileFactory->createUploadedFile->getError\" returned unexpected result.\n".
            "Action was called with parameters (error => $error).\n".
            "Expected result is \"$errorExpected\".\n".
            "Caught result is \"$errorCaught\"."
        );
    }
    /** **********************************************************************
     * Test "UploadedFileFactoryInterface::createUploadedFile" provides uploaded file
     * with expected client file name.
     *
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderUploadedFilesWithNameParams
     *
     * @param           resource    $resource           Resource.
     * @param           string|null $fileName           File name.
     * @param           string|null $fileNameExpected   File name expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testUploadedFileClientFilename(
                    $resource,
        string|null $fileName,
        string|null $fileNameExpected
    ): void
    {
        $stream         = new Stream($resource);
        $uploadedFile   = (new UploadedFileFactory())->createUploadedFile(
            $stream,
            null,
            0,
            $fileName
        );
        $fileNameCaught = $uploadedFile->getClientFilename();

        self::assertEquals(
            $fileNameExpected,
            $fileNameCaught,
            "Action \"UploadedFileFactory->createUploadedFile->getClientFilename\"".
            " returned unexpected result.\n".
            "Action was called with parameters (client file name => $fileName).\n".
            "Expected result is \"$fileNameExpected\".\n".
            "Caught result is \"$fileNameCaught\"."
        );
    }
    /** **********************************************************************
     * Test "UploadedFileFactoryInterface::createUploadedFile" provides uploaded file
     * with expected client media type.
     *
     * @covers          UploadedFileFactory::createUploadedFile
     * @dataProvider    dataProviderUploadedFilesWithMediaTypeParams
     *
     * @param           resource    $resource           Resource.
     * @param           string|null $mediaType          Media type.
     * @param           string|null $mediaTypeExpected  Media type expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testUploadedFileClientMediaType(
                    $resource,
        string|null $mediaType,
        string|null $mediaTypeExpected
    ): void
    {
        $stream             = new Stream($resource);
        $uploadedFile       = (new UploadedFileFactory())->createUploadedFile(
            $stream,
            null,
            0,
            null,
            $mediaType
        );
        $mediaTypeCaught    = $uploadedFile->getClientMediaType();

        self::assertEquals(
            $mediaTypeExpected,
            $mediaTypeCaught,
            "Action \"UploadedFileFactory->createUploadedFile->getClientFilename\"".
            " returned unexpected result.\n".
            "Action was called with parameters (client file name => $mediaType).\n".
            "Expected result is \"$mediaTypeExpected\".\n".
            "Caught result is \"$mediaTypeCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: valid uploaded files resources.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFilesResourcesValid(): array
    {
        return (new UploadedFileGetStreamTest())->dataProviderResources();
    }
    /** **********************************************************************
     * Data provider: invalid uploaded files resources.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFilesResourcesInvalid(): array
    {
        return (new UploadedFileConstructorTest())->dataProviderResourcesInvalid();
    }
    /** **********************************************************************
     * Data provider: uploaded files with size values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithSizeParams(): array
    {
        return (new UploadedFileGetSizeTest())->dataProviderUploadedFileWithSizeParams();
    }
    /** **********************************************************************
     * Data provider: uploaded files with errors values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFileWithErrorParams(): array
    {
        return (new UploadedFileGetErrorTest())->dataProviderUploadedFileWithErrorParams();
    }
    /** **********************************************************************
     * Data provider: uploaded files with client file name values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFilesWithNameParams(): array
    {
        return (new UploadedFileGetClientNameTest())->dataProviderUploadedFilesWithNameParams();
    }
    /** **********************************************************************
     * Data provider: uploaded files with client media type values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderUploadedFilesWithMediaTypeParams(): array
    {
        return (new UploadedFileGetClientMediaTypeTest())->dataProviderUploadedFilesWithMediaTypeParams();
    }
}