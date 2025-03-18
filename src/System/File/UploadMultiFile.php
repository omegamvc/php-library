<?php

declare(strict_types=1);

namespace System\File;

use System\File\Exceptions\FileNotExistsException;
use System\File\Exceptions\FileNotUploadedException;
use System\File\Exceptions\FolderNotExistsException;

class UploadMultiFile extends AbstarctUpload
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
            $this->fileName  = $files['name'];
            $this->fileType  = $files['type'];
            $this->fileTmp   = $files['tmp_name'];
            $this->fileError = $files['error'];
            $this->fileSize  = $files['size'];
            // parse file extention
            foreach ($files['name'] as $name) {
                $extension              = explode('.', $name);
                $this->fileExtension[] = strtolower(end($extension));
            }
        } else {
            $this->fileName[]  = $files['name'];
            $this->fileType[]  = $files['type'];
            $this->fileTmp[]   = $files['tmp_name'];
            $this->fileError[] = $files['error'];
            $this->fileSize[]  = $files['size'];
            // parse files extention
            $extension              = explode('.', $files['name']);
            $this->fileExtension[] = strtolower(end($extension));
        }

        $this->isMulti = true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empety when unsuccess upload
     */
    public function uploads()
    {
        return $this->stream();
    }

    /**
     * Get all uploaded files content.
     *
     * @return string[]
     */
    public function getAll()
    {
        if (!$this->success) {
            throw new FileNotUploadedException();
        }

        $contents = [];

        foreach ($this->fileExtension as $key => $extension) {
            $destination    =  $this->uploadLocation . $this->uploadName . $key . '.' . $extension;
            $content        = file_get_contents($destination);

            if (false === $content) {
                throw new FileNotExistsException($destination);
            }
            $contents[] = $content;
        }

        return $contents;
    }
}
