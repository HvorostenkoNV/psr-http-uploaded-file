<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFile;

use ValueError;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

/**
 * Uploaded file errors collection.
 */
enum UploadedFileError
{
    case OK;
    case ERROR_SIZE_INI;
    case ERROR_SIZE_FORM;
    case ERROR_PARTIAL;
    case ERROR_NO_FILE;
    case ERROR_NO_TMP_DIR;
    case ERROR_CANT_WRITE;
    case ERROR_EXTENSION;
    /**
     * Get value, which is exact as "UPLOAD" constants.
     */
    public function value(): int
    {
        return match ($this) {
            self::OK                => UPLOAD_ERR_OK,
            self::ERROR_SIZE_INI    => UPLOAD_ERR_INI_SIZE,
            self::ERROR_SIZE_FORM   => UPLOAD_ERR_FORM_SIZE,
            self::ERROR_PARTIAL     => UPLOAD_ERR_PARTIAL,
            self::ERROR_NO_FILE     => UPLOAD_ERR_NO_FILE,
            self::ERROR_NO_TMP_DIR  => UPLOAD_ERR_NO_TMP_DIR,
            self::ERROR_CANT_WRITE  => UPLOAD_ERR_CANT_WRITE,
            self::ERROR_EXTENSION   => UPLOAD_ERR_EXTENSION,
        };
    }

    /**
     * Get error message.
     */
    public function message(): string
    {
        return match ($this) {
            self::OK                => 'there is no error, the file uploaded with success',
            self::ERROR_SIZE_INI    => 'the uploaded file exceeds the upload_max_filesize'.
                ' directive in php.ini',
            self::ERROR_SIZE_FORM   => 'the uploaded file exceeds the MAX_FILE_SIZE directive'.
                ' that was specified in the HTML form',
            self::ERROR_PARTIAL     => 'the uploaded file was only partially uploaded',
            self::ERROR_NO_FILE     => 'no file was uploaded',
            self::ERROR_NO_TMP_DIR  => 'missing a temporary folder',
            self::ERROR_CANT_WRITE  => 'failed to write file to disk',
            self::ERROR_EXTENSION   => 'a PHP extension stopped the file upload',
        };
    }

    /**
     * @throws ValueError
     */
    public static function from(int $value): self
    {
        foreach (self::cases() as $case) {
            if ($case->value() === $value) {
                return $case;
            }
        }

        throw new ValueError("unknown case for value [{$value}]");
    }
}
