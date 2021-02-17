<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFile\Collection;

use HNV\Http\Helper\Collection\CollectionInterface;

use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_PARTIAL;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
/** ***********************************************************************************************
 * Uploaded file errors collection.
 *
 * @package HNV\Psr\Http\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFileError implements CollectionInterface
{
    public const STATUS_OK = UPLOAD_ERR_OK;
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return [
            UPLOAD_ERR_OK           => 'there is no error, the file uploaded with success',
            UPLOAD_ERR_INI_SIZE     => 'the uploaded file exceeds the upload_max_filesize'.
                ' directive in php.ini',
            UPLOAD_ERR_FORM_SIZE    => 'the uploaded file exceeds the MAX_FILE_SIZE directive'.
                ' that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL      => 'the uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE      => 'no file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR   => 'missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE   => 'failed to write file to disk',
            UPLOAD_ERR_EXTENSION    => 'a PHP extension stopped the file upload',
        ];
    }
}