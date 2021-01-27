<?php
declare(strict_types=1);

namespace HNV\Http\UploadedFile;

use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;
use Psr\Http\{
    Message\StreamInterface,
    Message\UploadedFileInterface
};
use HNV\Http\UploadedFile\Collection\UploadedFileError as UploadedFileErrorCollection;

use function is_int;
use function is_string;
use function is_uploaded_file;
use function strlen;
use function strpos;
use function in_array;
use function rename;
use function move_uploaded_file;
use function error_get_last;
use function php_sapi_name;
/** ***********************************************************************************************
 * PSR-7 UploadedFileInterface implementation.
 *
 * @package HNV\Psr\Http\UploadedFile
 * @author  Hvorostenko
 *************************************************************************************************/
class UploadedFile implements UploadedFileInterface
{
    private const UT_EMULATED_UPLOAD_FILES_KEY = 'UT_EMULATED_UPLOAD_FILES_KEY';

    private StreamInterface $stream;
    private int|null        $size               = null;
    private int             $error              = 0;
    private string|null     $clientFilename     = null;
    private string|null     $clientMediaType    = null;
    private bool            $isMoved            = false;
    /** **********************************************************************
     * Constructor.
     *
     * @param   StreamInterface $stream             The underlying stream representing
     *                                              the uploaded file content.
     * @param   int|null    $size                   The size of the file in bytes.
     * @param   int|null    $error                  The PHP file upload error.
     * @param   string|null $clientFilename         The filename as provided by
     *                                              the client, if any.
     * @param   string|null $clientMediaType        The media type as provided by
     *                                              the client, if any.
     *
     * @throws  InvalidArgumentException            Uploaded file is invalid.
     ************************************************************************/
    public function __construct
    (
        StreamInterface $stream,
        ?int            $size               = null,
        ?int            $error              = null,
        ?string         $clientFilename     = null,
        ?string         $clientMediaType    = null
    ) {
        try {
            $this->checkStreamIsValid($stream);
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException('stream is not valid', 0, $exception);
        }

        $errorsSet              = UploadedFileErrorCollection::get();

        $this->stream           = $stream;
        $this->size             = is_int($size) && $size > 0
            ? $size
            : null;
        $this->error            = is_int($error) && isset($errorsSet[$error])
            ? $error
            : UploadedFileErrorCollection::STATUS_OK;
        $this->clientFilename   = is_string($clientFilename) && strlen($clientFilename) > 0
            ? $clientFilename
            : null;
        $this->clientMediaType  = is_string($clientMediaType) && strlen($clientMediaType) > 0
            ? $clientMediaType
            : null;
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function getStream(): StreamInterface
    {
        if ($this->isMoved) {
            throw new RuntimeException('file has been already moved');
        }

        try {
            $this->checkStreamIsValid($this->stream);

            return $this->stream;
        } catch (RuntimeException $exception) {
            throw new RuntimeException('no stream is available', 0, $exception);
        }
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function moveTo(string $targetPath): void
    {
        $error          = $this->getError();
        $errorMessage   = UploadedFileErrorCollection::get()[$error] ?? '';

        if ($error != UploadedFileErrorCollection::STATUS_OK) {
            throw new RuntimeException("uploaded file cannot be moved with error $errorMessage");
        }
        if ($this->isMoved) {
            throw new RuntimeException('file has been already moved!');
        }
        if (strlen($targetPath) === 0) {
            throw new InvalidArgumentException('target path is empty');
        }

        try {
            $this->checkTargetPathForReplacingIsValid($targetPath);
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException('target path is invalid', 0, $exception);
        }

        try {
            if (self::checkSapiEnvironmentExist()) {
                $this->moveStreamWithSapi($this->stream, $targetPath);
            } else {
                $this->moveFileWithoutSapi($this->stream, $targetPath);
            }

            $this->isMoved = true;
        } catch (RuntimeException $exception) {
            throw new RuntimeException('file moving failed', 0, $exception);
        }
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function getSize(): ?int
    {
        return $this->size;
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function getError(): int
    {
        return $this->error;
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
    /** **********************************************************************
     * Check SAPI environment exist.
     *
     * @return  bool                                SAPI environment exists.
     ************************************************************************/
    private static function checkSapiEnvironmentExist(): bool
    {
        $sapi = php_sapi_name();

        return
            strlen($sapi) > 0 &&
            strpos($sapi, 'cli') !== 0 &&
            strpos($sapi, 'phpdbg') !== 0;
    }
    /** **********************************************************************
     * Check upload file stream is valid.
     *
     * @param   StreamInterface $stream             Stream.
     *
     * @return  void
     * @throws  RuntimeException                    Stream is invalid.
     ************************************************************************/
    private function checkStreamIsValid(StreamInterface $stream): void
    {
        $filePath   = (string) $stream->getMetadata('uri');
        $file       = new SplFileInfo($filePath);

        if (!$file->isFile()) {
            throw new RuntimeException('file is not exist');
        }
        if (!$stream->isReadable()) {
            throw new RuntimeException('stream is not readable');
        }
        if (!$this->isUnitTestUploadFile($filePath) && !is_uploaded_file($filePath)) {
            throw new RuntimeException('stream is not uploaded file');
        }
    }
    /** **********************************************************************
     * Check target path for file replacing is valid.
     *
     * @param   string $targetPath                  Target path for file replacing.
     *
     * @return  void
     * @throws  RuntimeException                    Target path is invalid.
     ************************************************************************/
    private function checkTargetPathForReplacingIsValid(string $targetPath): void
    {
        $file       = new SplFileInfo($targetPath);
        $directory  = new SplFileInfo($file->getPath());

        if (!$directory->isDir()) {
            throw new RuntimeException("directory {$file->getPath()} is not exist");
        }
        if (!$directory->isWritable()) {
            throw new RuntimeException("directory {$file->getPath()} is not writable");
        }
    }
    /** **********************************************************************
     * Move stream to a new location with SAPI usage.
     *
     * @param   StreamInterface $stream             Stream.
     * @param   string          $targetPath         Path to which to move the uploaded file.
     *
     * @return  void
     * @throws  RuntimeException                    Moving process failed.
     ************************************************************************/
    private function moveStreamWithSapi(StreamInterface $stream, string $targetPath): void
    {
        $fileCurrentPath    = $stream->getMetadata('uri');
        $replacingSuccess   = move_uploaded_file($fileCurrentPath, $targetPath);

        if (!$replacingSuccess) {
            $lastErrorData  = error_get_last();
            $errorMessage   = $lastErrorData['message'] ?? 'unknown error';

            throw new RuntimeException($errorMessage);
        }
    }
    /** **********************************************************************
     * Move stream to a new location without SAPI usage.
     *
     * @param   StreamInterface $stream             Stream.
     * @param   string          $targetPath         Path to which to move the uploaded file.
     *
     * @return  void
     * @throws  RuntimeException                    Moving process failed.
     ************************************************************************/
    private function moveFileWithoutSapi(StreamInterface $stream, string $targetPath): void
    {
        $fileCurrentPath    = $stream->getMetadata('uri');
        $replacingSuccess   = rename($fileCurrentPath, $targetPath);

        if (!$replacingSuccess) {
            $lastErrorData  = error_get_last();
            $errorMessage   = $lastErrorData['message'] ?? 'unknown error';

            throw new RuntimeException($errorMessage);
        }
    }
    /** **********************************************************************
     * Check file is UnitTest upload file emulation.
     *
     * @param   string $filePath                    File path.
     *
     * @return  bool                                Is UnitTest uploaded file.
     ************************************************************************/
    private function isUnitTestUploadFile(string $filePath): bool
    {
        $emulatedRegisteredFiles = (array) ($GLOBALS[self::UT_EMULATED_UPLOAD_FILES_KEY] ?? []);

        return in_array($filePath, $emulatedRegisteredFiles);
    }
}