<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private string $targetDirectory;
    private Filesystem $filesystem;

    public function __construct(string $uploadsDir)
    {
        $this->targetDirectory = $uploadsDir;
        $this->filesystem = new Filesystem();
    }

    public function upload(UploadedFile $file, string $newFilename): string
    {
        $filePath = $this->targetDirectory . '/' . $newFilename;

        // Upload the new file
        $file->move($this->targetDirectory, $newFilename);

        return $filePath;
    }

    public function delete(string $filePath): void
    {
        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }
}
