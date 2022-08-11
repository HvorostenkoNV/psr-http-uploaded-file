<?php

declare(strict_types=1);

namespace HNV\Http\UploadedFileTests;

use HNV\Http\Helper\Collection\Resource\{
    AccessMode,
    AccessModeType,
};
use HNV\Http\Helper\Generator\{
    Directory   as DirectoryGenerator,
    File        as FileGenerator,
    Resource    as ResourceGenerator,
};
use HNV\Http\UploadedFileTests\Generator\{
    GeneratedUploadedFileData,
    UploadedFile as UploadedFileGenerator,
};
use PHPUnit\Framework\TestCase;

abstract class AbstractUploadedFileTestCase extends TestCase
{
    public function dataProviderResourcesValid(): iterable
    {
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [$uploadedFileResource];
        }
    }

    public function dataProviderResourcesInvalid(): iterable
    {
        foreach ($this->getResourceAccessModesInvalid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->tmpName, $mode);

            yield [$uploadedFileResource];
        }

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $file               = $this->generateFile();
            $commonFileResource = $this->generateResource($file, $mode);

            yield [$commonFileResource];
        }
    }

    /**
     * @return AccessMode[]
     */
    protected function getResourceAccessModesValid(): array
    {
        return AccessMode::get(
            AccessModeType::READABLE_AND_WRITABLE,
            AccessModeType::EXPECT_NO_FILE
        );
    }

    /**
     * @return AccessMode[]
     */
    protected function getResourceAccessModesInvalid(): array
    {
        return AccessMode::get(
            AccessModeType::WRITABLE_ONLY,
            AccessModeType::EXPECT_NO_FILE
        );
    }

    protected function generateFile(): string
    {
        return (new FileGenerator())->generate();
    }

    protected function generateDirectory(): string
    {
        return (new DirectoryGenerator())->generate();
    }

    protected function generateUploadedFile(): GeneratedUploadedFileData
    {
        return (new UploadedFileGenerator())->generate();
    }

    /**
     * @return resource
     */
    protected function generateResource(string $filePath, AccessMode $mode): mixed
    {
        return (new ResourceGenerator($filePath, $mode))->generate();
    }
}
