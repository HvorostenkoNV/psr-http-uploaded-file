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

/**
 * Abstract uploaded file test class.
 *
 * Provides several helpfully methods.
 */
abstract class AbstractUploadedFileTest extends TestCase
{
    /**
     * Data provider: valid/suitable uploaded files resources.
     */
    public function dataProviderResourcesValid(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesValid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [$uploadedFileResource];
        }

        return $result;
    }

    /**
     * Data provider: invalid/not suitable uploaded files resources.
     */
    public function dataProviderResourcesInvalid(): array
    {
        $result = [];

        foreach ($this->getResourceAccessModesInvalid() as $mode) {
            $uploadedFile           = $this->generateUploadedFile();
            $uploadedFileResource   = $this->generateResource($uploadedFile->getTmpName(), $mode);
            $result[]               = [$uploadedFileResource];
        }
        foreach ($this->getResourceAccessModesValid() as $mode) {
            $file                   = $this->generateFile();
            $commonFileResource     = $this->generateResource($file, $mode);
            $result[]               = [$commonFileResource];
        }

        return $result;
    }

    /**
     * Get resource access modes set, valid for tests.
     *
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
     * Get resource access modes set, invalid for tests.
     *
     * @return AccessMode[]
     */
    protected function getResourceAccessModesInvalid(): array
    {
        return AccessMode::get(
            AccessModeType::WRITABLE_ONLY,
            AccessModeType::EXPECT_NO_FILE
        );
    }

    /**
     * Generate file and get it`s path.
     */
    protected function generateFile(): string
    {
        return (new FileGenerator())->generate();
    }

    /**
     * Generate directory and get it`s path.
     */
    protected function generateDirectory(): string
    {
        return (new DirectoryGenerator())->generate();
    }

    /**
     * Generate uploaded file and get it`s data.
     */
    protected function generateUploadedFile(): GeneratedUploadedFileData
    {
        return (new UploadedFileGenerator())->generate();
    }

    /**
     * Generate resource.
     *
     * @return resource
     */
    protected function generateResource(string $filePath, AccessMode $mode): mixed
    {
        return (new ResourceGenerator($filePath, $mode))->generate();
    }
}
