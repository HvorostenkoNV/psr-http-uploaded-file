<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests\Generator;

use HNV\Http\Helper\Generator\{
    ClearableGenerator,
    File    as FileGenerator,
    GeneratorInterface,
    Text    as TextGenerator,
};
use HNV\Http\UploadedFile\{
    UploadedFile as UploadedFileSubject,
    UploadedFileError,
};
use LogicException;

use function array_pop;
use function array_rand;
use function array_search;
use function explode;
use function file_put_contents;
use function is_file;
use function strlen;
use function unlink;

use const DIRECTORY_SEPARATOR;

class UploadedFile extends ClearableGenerator implements GeneratorInterface
{
    private const FILE_TYPES = [
        'txt'   => 'text/plain',
        'jpg'   => 'image/jpeg',
        'pdf'   => 'application/pdf',
    ];

    /**
     * {@inheritDoc}
     */
    public function generate(): GeneratedUploadedFileData
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

        $this->clear(function () use ($fileFullName): void {
            $this->unregisterUploadedFile($fileFullName);

            if (is_file($fileFullName)) {
                unlink($fileFullName);
            }
        });

        return new GeneratedUploadedFileData(
            $fileShortName,
            $fileMimeType,
            $fileFullName,
            UploadedFileError::OK,
            $fileSize
        );
    }

    private function registerUploadedFile(string $filePath): void
    {
        $index              = UploadedFileSubject::UT_EMULATED_UPLOAD_FILES_KEY;
        $GLOBALS[$index] ??= [];
        $GLOBALS[$index][]  = $filePath;
    }

    private function unregisterUploadedFile(string $filePath): void
    {
        $index          = UploadedFileSubject::UT_EMULATED_UPLOAD_FILES_KEY;
        $arraySearch    = array_search($filePath, $GLOBALS[$index], true);

        unset($GLOBALS[$index][$arraySearch]);
    }
}
