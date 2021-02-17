<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use Throwable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\{
    Resource    as ResourceGenerator,
    File        as FileGenerator
};
use HNV\Http\UploadedFileTests\Generator\UploadedFile as UploadedFileGenerator;
use HNV\Http\UploadedFileTests\Collection\ResourceAccessMode\{
    WritableOnly        as ResourceAccessModeWritableOnly,
    ReadableAndWritable as ResourceAccessModeReadableAndWritable
};
use HNV\Http\{
    Stream\Stream,
    UploadedFile\UploadedFile
};
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation test.
 *
 * Testing constructor behavior.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileConstructorTest extends TestCase
{
    /** **********************************************************************
     * Test "UploadedFile::__construct" throws exception with invalid stream.
     *
     * @covers          UploadedFile::__constructor
     * @dataProvider    dataProviderResourcesInvalid
     *
     * @param           resource $resource      Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testConstructorThrowsException($resource): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stream = new Stream($resource);
        new UploadedFile($stream);

        self::fail(
            "Action \"UploadedFile->__construct\" threw no expected exception.\n".
            "Action was called with parameters (stream => invalid stream).\n".
            "Expects \"InvalidArgumentException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Data provider: invalid/not suitable uploaded files resources.
     *
     * @return  array                           Data.
     ************************************************************************/
    public function dataProviderResourcesInvalid(): array
    {
        $result = [];

        foreach (ResourceAccessModeWritableOnly::get() as $mode) {
            $uploadedFile           = (new UploadedFileGenerator())->generate();
            $uploadedFileResource   = (new ResourceGenerator(
                $uploadedFile['tmp_name'],
                $mode)
            )->generate();
            $result[]               = [$uploadedFileResource];
        }
        foreach (ResourceAccessModeReadableAndWritable::get() as $mode) {
            $file                   = (new FileGenerator())->generate();
            $commonFileResource     = (new ResourceGenerator($file, $mode))->generate();
            $result[]               = [$commonFileResource];
        }

        return $result;
    }
}