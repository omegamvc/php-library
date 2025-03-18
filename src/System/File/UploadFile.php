<?php

declare(strict_types=1);

namespace System\File;

use System\File\Exceptions\FileNotExistsException;
use System\File\Exceptions\FileNotUploadedException;
use System\File\Exceptions\FolderNotExistsException;
use System\File\Exceptions\MultiFileUploadDetectException;

class UploadFile extends AbstarctUpload
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
    public function setFolderLocation(string $folder_location): self
    {
        if (!is_dir($folder_location)) {
            throw new FolderNotExistsException($folder_location);
        }

        $this->uploadLocation = $folder_location;

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
    public function markTest(bool $mark_upload_test): self
    {
        $this->test = $mark_upload_test;

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
        // parse files extention
        $extension              = explode('.', $files['name']);
        $this->fileExtension[] = strtolower(end($extension));
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string File location on success upload file, sting empety when unsuccess upload
     */
    public function upload()
    {
        return $this->stream()[0] ?? '';
    }

    /**
     * Get all uploaded files content.
     *
     * @return string
     */
    public function get()
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
