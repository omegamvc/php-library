<?php

declare(strict_types=1);

namespace Omega\Http\Upload;

/**
 * This class use for upload file to server using move_uploaded_file() function,
 * make with easy use and manifest, every one can use and modify this class to improve performance.
 *
 * @author sonypradana@gmail.com
 */
abstract class AbstractUpload
{
    /**
     * Cath files form upload file.
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
    protected bool $_isset = false;

    /**
     * Detect test mode.
     *
     * @var bool
     */
    protected bool $_test = false;

    /**
     * Detect single or multi upload files.
     *
     * @var bool True if multi file upload
     */
    protected bool $_is_multi = false;

    // property file --------------------------------------------

    /** @var string[] */
    protected array $file_name;
    /** @var string[] Original file category */
    protected array $file_type;
    /** @var string[] Original file temp location */
    protected array $file_tmp;
    /** @var int[] Original file error status code */
    protected array $file_error;
    /** @var int[] Original file size in byte */
    protected array $file_size;
    /** @var string[] Original file extension */
    protected array $file_extension;

    // property upload ------------------------------------------

    /** @var string Upload file name (without extension) */
    protected string $upload_name;
    /** @var string Upload file to save location */
    protected string $upload_location = '/';
    /** @var array<int, string> Upload allow file extension */
    protected array $upload_types    = ['jpg', 'jpeg', 'png'];
    /** @var array<int, string> Upload allow file mime type */
    protected array $upload_mime     = ['image/jpg', 'image/jpeg', 'image/png'];
    /** @var int Upload maximals file size */
    protected int $upload_size_max = 50000;

    /**
     * Provide error message.
     *
     * @var string
     */
    protected string $_error_message = '';

    // setter ------------------------------------------------

    /**
     * Set file name (without extension).
     * File name will convert to allow string url.
     *
     * @param string $file_name File name (without extension)
     */
    abstract public function setFileName(string $file_name): self;

    /**
     * File to save/upload location (server folder),
     * Warning:: not creat new folder if location not exist.
     *
     * @param string $folder_location Upload file to save location
     */
    abstract public function setFolderLocation(string $folder_location): self;

    /**
     * List allow file extension to upload.
     *
     * @param array<int, string> $extensions list extension file
     */
    abstract public function setFileTypes(array $extensions): self;

    /**
     * List allow file mime type to upload.
     *
     * @param array<int, string> $mimes list mime type file
     */
    abstract public function setMimeTypes(array $mimes): self;

    /**
     * Maximum file size to upload (in byte).
     *
     * @param int $byte maximum file size upload
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
        return $this->_error_message;
    }

    /**
     * Get uploaded file types.
     *
     * @return array<int, string>
     */
    public function getFileTypes(): array
    {
        return $this->upload_types;
    }

    /**
     * Creat New file upload to server.
     *
     * @param array<string, string|int|array<string>|array<int>> $files Super global FILE (single array)
     */
    public function __construct(array $files)
    {
        // random files name by default
        $this->upload_name = uniqid('uploaded_'); // files name without extension

        $this->files = $files;
    }

    /**
     * Helper to validate file upload base on configure
     * - check file error
     * - check extension / mime (optional)
     * - check maximum size.
     *
     * also return error message
     *
     * @return bool True on error found not found
     */
    protected function validate(): bool
    {
        // check file error
        foreach ($this->file_error as $error) {
            $file_error = $error === 4;
            if ($file_error) {
                $this->_error_message = 'no file upload';

                return false;
            }
        }

        // check file type (upload_type must set)
        foreach ($this->file_extension as $extension) {
            $extensionError = !in_array($extension, $this->upload_types);
            if ($extensionError) {
                $this->_error_message = 'file type not support';

                return false;
            }
        }

        // check mime type (upload_mime must set)
        foreach ($this->file_type as $type) {
            $mime_error = !in_array($type, $this->upload_mime);
            if ($mime_error) {
                $this->_error_message = 'file type not support';

                return false;
            }
        }

        // check file size
        foreach ($this->file_size as $size) {
            $is_size_error = $size > $this->upload_size_max;
            if ($is_size_error) {
                $this->_error_message = 'file size too large';

                return false;
            }
        }

        $this->_error_message = 'success';

        return true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empty when unsuccess upload
     */
    protected function stream(): array
    {
        // isset property, enable when data has been validate
        $this->_isset = true;
        $destinations = [];

        if (!$this->validate()) {
            return $destinations;
        }

        if ($this->_test) {
            foreach ($this->file_extension as $key => $extension) {
                $suffix         = $this->_is_multi ? $key : '';
                $destination    =  $this->upload_location . $this->upload_name . $suffix . '.' . $extension;
                $this->success = copy($this->file_tmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }

        if ($this->_test === false) {
            foreach ($this->file_extension as $key => $extension) {
                $suffix         = $this->_is_multi ? $key : '';
                $destination    =  $this->upload_location . $this->upload_name . $suffix . '.' . $extension;
                $this->success = move_uploaded_file($this->file_tmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }
    }

    /**
     * Helper to delete file if needed.
     *
     * @return bool True on success deleted file
     */
    public function delete(string $url): bool
    {
        return file_exists($url) && unlink($url);
    }

    /**
     * Helper to creat new folder if needed.
     *
     * @return bool True on success created folder
     */
    public function creatFolder(string $path): bool
    {
        return !file_exists($path) && mkdir($path, 0777, true);
    }
}
