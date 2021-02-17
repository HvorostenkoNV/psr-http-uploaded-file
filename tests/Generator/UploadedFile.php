<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFileTests\Generator;

use LogicException;
use HNV\Http\Helper\Generator\{
    GeneratorInterface,
    AbstractGenerator,
    File    as FileGenerator,
    Text    as TextGenerator
};
use HNV\Http\UploadedFile\Collection\UploadedFileError as UploadedFileErrorCollection;

use function is_file;
use function explode;
use function strlen;
use function array_pop;
use function array_rand;
use function file_put_contents;
use function unlink;

use const DIRECTORY_SEPARATOR;
/** ***********************************************************************************************
 * Upload file generator.
 *
 * @package HNV\Psr\Http\Tests\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFile extends AbstractGenerator implements GeneratorInterface
{
    private const UPLOAD_FILES_REGISTRATION_KEY = 'UT_EMULATED_UPLOAD_FILES_KEY';
    private const FILE_TYPES                    = [
        'txt'   => 'text/plain',
        'jpg'   => 'image/jpeg',
        'pdf'   => 'application/pdf',
    ];
    /** **********************************************************************
     * @inheritDoc
     *
     * @return array                        Uploaded file data.
     * @example
     *         [
     *          name        => file name,
     *          type        => file MIME type,
     *          tmp_name    => file full path,
     *          error       => uploaded file error,
     *          size        => file size,
     *         ]
     ************************************************************************/
    public function generate(): array
    {
        $fileExtension          = array_rand(self::FILE_TYPES);
        $fileMimeType           = self::FILE_TYPES[$fileExtension];
        $fileData               = (new TextGenerator())->generate();
        $fileSize               = strlen($fileData);

        $fileFullName           = (new FileGenerator($fileExtension))->generate();
        $fileFullNameExploded   = explode(DIRECTORY_SEPARATOR, $fileFullName);
        $fileShortName          = array_pop($fileFullNameExploded);
        $dataWritingSuccess     = file_put_contents($fileFullName, $fileData);

        if ($dataWritingSuccess === false) {
            throw new LogicException('file data writing failed');
        }

        $this->registerUploadedFile($fileFullName);

        $this->clear(function() use ($fileFullName) {
            if (is_file($fileFullName)) {
                unlink($fileFullName);
            }
        });

        return [
            'name'      => $fileShortName,
            'type'      => $fileMimeType,
            'tmp_name'  => $fileFullName,
            'error'     => UploadedFileErrorCollection::STATUS_OK,
            'size'      => $fileSize
        ];
    }
    /** **********************************************************************
     * Register file and mark it as UnitTests temporary uploaded file.
     *
     * @param   string $filePath            File path.
     *
     * @return  void
     ************************************************************************/
    private function registerUploadedFile(string $filePath): void
    {
        $index              = self::UPLOAD_FILES_REGISTRATION_KEY;
        $GLOBALS[$index]    = $GLOBALS[$index] ?? [];
        $GLOBALS[$index][]  = $filePath;
    }
}