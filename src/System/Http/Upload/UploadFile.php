<?php

declare(strict_types=1);

namespace System\Http\Upload;

use System\Http\Exceptions\FileNotExistsException;
use System\Http\Exceptions\FileNotUploadedException;
use System\Http\Exceptions\FolderNotExistsException;
use System\Http\Exceptions\MultiFileUploadDetectException;
use function end;
use function explode;
use function file_get_contents;
use function is_array;
use function is_dir;
use function strtolower;
use function urlencode;

/** {@inheritDoc} */
class UploadFile extends AbstractUpload
{
    /**
     * {@inheritDoc}
     */
    public function setFileName(string $fileName): self
    {
        // file name without extension
        $fileName         = urlencode($fileName);
        $this->uploadName = $fileName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFolderLocation(string $folderLocation): self
    {
        if (!is_dir($folderLocation)) {
            throw new FolderNotExistsException($folderLocation);
        }

        $this->uploadLocation = $folderLocation;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFileTypes(array $extensions): self
    {
        $this->uploadTypes = $extensions;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMimeTypes(array $mimes): self
    {
        $this->uploadMime = $mimes;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxFileSize(int $byte): self
    {
        $this->uploadSizeMax = $byte;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function markTest(bool $markUploadTest): self
    {
        $this->test = $markUploadTest;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(array $files)
    {
        parent::__construct($files);

        if (is_array($files['name'])) {
            throw new MultiFileUploadDetectException();
        }

        $this->fileName[]  = $files['name'];
        $this->fileType[]  = $files['type'];
        $this->fileTmp[]   = $files['tmp_name'];
        $this->fileError[] = $files['error'];
        $this->fileSize[]  = $files['size'];
        // parse files extension
        $extension             = explode('.', $files['name']);
        $this->fileExtension[] = strtolower(end($extension));
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string File location on success upload file, sting empty when unsuccessful upload
     */
    public function upload(): string
    {
        return $this->stream()[0] ?? '';
    }

    /**
     * Get all uploaded files content.
     *
     * @return string
     */
    public function get(): string
    {
        $destination =  $this->uploadLocation . $this->uploadName . '.' . $this->fileExtension[0];

        if (!$this->success) {
            throw new FileNotUploadedException();
        }

        $content = file_get_contents($destination);
        if (false === $content) {
            throw new FileNotExistsException($destination);
        }

        return $content;
    }
}
