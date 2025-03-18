<?php

declare(strict_types=1);

namespace System\File;

/**
 * This class use for uplaod file to server using move_uploaded_file() function,
 * make with easy use and manitens, every one can use and modifi this class to improve performense.
 */
abstract class AbstarctUpload
{
    /**
     * Catch files form upload file.
     *
     * @var array<string, array<int, string>|string>
     */
    protected array $files;

    /**
     *  File upload status.
     *
     * @var bool
     */
    protected bool $success = false;

    /**
     * File has execute to upload.
     *
     * @var bool
     */
    protected bool $isset = false;

    /**
     * Detect test mode.
     *
     * @var bool
     */
    protected bool $test = false;

    /**
     * Detect single or multi upload files.
     *
     * @var bool True if multi file upload
     */
    protected bool $isMulti = false;

    // property file --------------------------------------------

    /** @var string[] */
    protected array $fileName;

    /** @var string[] Original file category */
    protected array $fileType;

    /** @var string[] Original file temp location */
    protected array $fileTmp;

    /** @var int[] Original file error status code */
    protected array $fileError;

    /** @var int[] Original file size in byte */
    protected array $fileSize;

    /** @var string[] Original file extension */
    protected array $fileExtension;

    // property upload ------------------------------------------

    /** @var string Upload file name (without extention) */
    protected string $uploadName;

    /** @var string Upload file to save location */
    protected string $uploadLocation = '/';

    /** @var array<int, string> Upload allow file extension */
    protected array $uploadTypes = ['jpg', 'jpeg', 'png'];

    /** @var array<int, string> Upload allow file mime type */
    protected array $uploadMime = ['image/jpg', 'image/jpeg', 'image/png'];

    /** @var int Upload maximum file size */
    protected int $uploadSizeMax = 50000;

    /**
     * Provide error message.
     *
     * @var string
     */
    protected string $errorMessage = '';

    // setter ------------------------------------------------

    /**
     * Set file name (without extention).
     * File name will convert to allow string url.
     *
     * @param string $fileName File name (without extention)
     */
    abstract public function setFileName(string $fileName): self;

    /**
     * File to save/upload location (server folder),
     * Warning:: not creat new folder if location not exis.
     *
     * @param string $folder_location Upload file to save location
     */
    abstract public function setFolderLocation(string $folder_location): self;

    /**
     * List allow file extension to upload.
     *
     * @param array<int, string> $extensions list extention file
     */
    abstract public function setFileTypes(array $extensions): self;

    /**
     * List allow file mime type to upload.
     *
     * @param array<int, string> $mimes list mime type file
     */
    abstract public function setMimeTypes(array $mimes): self;

    /**
     * Maksimum file size to upload (in byte).
     *
     * @param int $byte maksimum file size upload
     */
    abstract public function setMaxFileSize(int $byte): self;

    /**
     * If true, upload determinate using `copy` instance of `move_uploaded_file`.
     *
     * @param bool $mark_upload_test true use copy file
     */
    abstract public function markTest(bool $mark_upload_test): self;

    // getter --------------------------------------------------------------

    /**
     * File Upload status.
     *
     * @return bool True on file upload success
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * Error message file upload status.
     *
     * @return string Give url file location
     */
    public function getError(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get uploaded file types.
     *
     * @return array<int, string>
     */
    public function getFileTypes()
    {
        return $this->uploadTypes;
    }

    /**
     * Creat New file upload to sarver.
     *
     * @param array<string, string|int|array<string>|array<int>> $files Super global FILE (single array)
     */
    public function __construct($files)
    {
        // random files name by default
        $this->uploadName = uniqid('uploaded_'); // files name without extension

        $this->files = $files;
    }

    /**
     * Helper to validate file upload base on configure
     * - cek file error
     * - cek extention / mime (optional)
     * - cek maskimum size.
     *
     * also return error message
     *
     * @return bool True on error found not found
     */
    protected function validate(): bool
    {
        // cek file error
        foreach ($this->fileError as $error) {
            $file_error = $error === 4 ? true : false;
            if ($file_error) {
                $this->errorMessage = 'no file upload';

                return false;
            }
        }

        // cek file type (upload_type must set)
        foreach ($this->fileExtension as $extension) {
            $extensio_error = in_array($extension, $this->uploadTypes) ? false : true;
            if ($extensio_error) {
                $this->errorMessage = 'file type not support';

                return false;
            }
        }

        // cek mime type (upload_mime must set)
        foreach ($this->fileType as $type) {
            $mime_error = in_array($type, $this->uploadMime) ? false : true;
            if ($mime_error) {
                $this->errorMessage = 'file type not support';

                return false;
            }
        }

        // cek file size
        foreach ($this->fileSize as $size) {
            $is_size_error = $size > $this->uploadSizeMax ? true : false;
            if ($is_size_error) {
                $this->errorMessage = 'file size too large';

                return false;
            }
        }

        $this->errorMessage = 'success';

        return true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empety when unsuccess upload
     */
    protected function stream()
    {
        // isset property, enable when data has been validate
        $this->isset = true;
        $destinations = [];

        if (!$this->validate()) {
            return $destinations;
        }

        if ($this->test) {
            foreach ($this->fileExtension as $key => $extension) {
                $surfix         = $this->isMulti ? $key : '';
                $destination    =  $this->uploadLocation . $this->uploadName . $surfix . '.' . $extension;
                $this->success = copy($this->fileTmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }

        if ($this->test === false) {
            foreach ($this->fileExtension as $key => $extension) {
                $surfix         = $this->isMulti ? $key : '';
                $destination    =  $this->uploadLocation . $this->uploadName . $surfix . '.' . $extension;
                $this->success = move_uploaded_file($this->fileTmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }
    }

    /**
     * Helper to delete file if needed.
     *
     * @return bool True on succes deleted file
     */
    public function delete(string $url): bool
    {
        return file_exists($url)
            ? unlink($url)
            : false;
    }

    /**
     * Helper to creat new folder if needed.
     *
     * @return bool True on succes created folder
     */
    public function creatFolder(string $path): bool
    {
        return !file_exists($path)
            ? mkdir($path, 0777, true)
            : false;
    }
}
