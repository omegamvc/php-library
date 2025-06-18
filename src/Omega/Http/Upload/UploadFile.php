<?php

declare(strict_types=1);

namespace Omega\Http\Upload;

use Omega\Http\Exceptions\FileNotExistsException;
use Omega\Http\Exceptions\FileNotUploadedException;
use Omega\Http\Exceptions\FolderNotExistsException;
use Omega\Http\Exceptions\MutiFileUploadDetectException;

class UploadFile extends AbstractUpload
{
    /**
     * {@inheritDoc}
     */
    public function setFileName(string $file_name): self
    {
        // file name without extension
        $file_name         = urlencode($file_name);
        $this->upload_name = $file_name;

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

        $this->upload_location = $folder_location;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFileTypes(array $extensions): self
    {
        $this->upload_types = $extensions;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMimeTypes(array $mimes): self
    {
        $this->upload_mime = $mimes;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxFileSize(int $byte): self
    {
        $this->upload_size_max = $byte;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function markTest(bool $mark_upload_test): self
    {
        $this->_test = $mark_upload_test;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(array $files)
    {
        parent::__construct($files);

        if (is_array($files['name'])) {
            throw new MutiFileUploadDetectException();
        }

        $this->file_name[]  = $files['name'];
        $this->file_type[]  = $files['type'];
        $this->file_tmp[]   = $files['tmp_name'];
        $this->file_error[] = $files['error'];
        $this->file_size[]  = $files['size'];
        // parse files extension
        $extension              = explode('.', $files['name']);
        $this->file_extension[] = strtolower(end($extension));
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string File location on success upload file, string empty when unsuccess upload
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
        $destination =  $this->upload_location . $this->upload_name . '.' . $this->file_extension[0];

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
