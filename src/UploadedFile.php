<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFile;

use InvalidArgumentException;
use Psr\Http\{
    Message\StreamInterface,
    Message\UploadedFileInterface,
};
use RuntimeException;
use SplFileInfo;

use function error_get_last;
use function in_array;
use function is_uploaded_file;
use function move_uploaded_file;
use function rename;
use function strlen;

use const PHP_SAPI;

/**
 * PSR-7 UploadedFileInterface implementation.
 */
class UploadedFile implements UploadedFileInterface
{
    private const UT_EMULATED_UPLOAD_FILES_KEY = 'UT_EMULATED_UPLOAD_FILES_KEY';

    private bool $isMoved               = false;
    private bool $sapiEnvironmentExist  = false;

    /**
     * @throws InvalidArgumentException uploaded file is invalid
     */
    public function __construct(
        private readonly StreamInterface $stream,
        private ?int $size = null,
        private readonly UploadedFileError $error = UploadedFileError::OK,
        private ?string $clientFilename = null,
        private ?string $clientMediaType = null
    ) {
        try {
            $this->checkStreamIsValid();
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException('stream is not valid', 0, $exception);
        }

        $this->size            = $this->size === 0 ? null : $this->size;
        $this->clientFilename  = $this->clientFilename === '' ? null : $this->clientFilename;
        $this->clientMediaType = $this->clientMediaType === '' ? null : $this->clientMediaType;

        $sapi                       = PHP_SAPI;
        $this->sapiEnvironmentExist = strlen($sapi) > 0
            && !str_starts_with($sapi, 'cli')
            && !str_starts_with($sapi, 'phpdbg');
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->isMoved) {
            throw new RuntimeException('file has been already moved');
        }

        try {
            $this->checkStreamIsValid();

            return $this->stream;
        } catch (RuntimeException $exception) {
            throw new RuntimeException('no stream is available', 0, $exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo(string $targetPath): void
    {
        if ($this->error !== UploadedFileError::OK) {
            throw new RuntimeException('uploaded file can not be moved'.
                " with error [{$this->error->message()}]");
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
            $this->moveStream($targetPath);
            $this->isMoved = true;
        } catch (RuntimeException $exception) {
            throw new RuntimeException('file moving failed', 0, $exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): int
    {
        return $this->error->value();
    }

    /**
     * {@inheritDoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * @throws RuntimeException
     */
    private function checkStreamIsValid(): void
    {
        $filePath   = (string) $this->stream->getMetadata('uri');
        $file       = new SplFileInfo($filePath);

        if (!$file->isFile()) {
            throw new RuntimeException('stream underlying file is not exist');
        }
        if (!$this->stream->isReadable()) {
            throw new RuntimeException('stream is not readable');
        }
        if (!$this->isUnitTestUploadFile($filePath) && !is_uploaded_file($filePath)) {
            throw new RuntimeException('stream is not an uploaded file');
        }
    }

    /**
     * @throws RuntimeException
     */
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

    /**
     * @throws RuntimeException moving process failed
     */
    private function moveStream(string $targetPath): void
    {
        $fileCurrentPath    = $this->stream->getMetadata('uri');
        $replacingSuccess   = $this->sapiEnvironmentExist
            ? move_uploaded_file($fileCurrentPath, $targetPath)
            : rename($fileCurrentPath, $targetPath);

        if (!$replacingSuccess) {
            $lastErrorData  = error_get_last();
            $errorMessage   = $lastErrorData['message'] ?? 'unknown error';

            throw new RuntimeException($errorMessage);
        }
    }

    /**
     * Check file is UnitTest upload file emulation.
     */
    private function isUnitTestUploadFile(string $filePath): bool
    {
        $emulatedRegisteredFiles = (array) ($GLOBALS[self::UT_EMULATED_UPLOAD_FILES_KEY] ?? []);

        return in_array($filePath, $emulatedRegisteredFiles, true);
    }
}
