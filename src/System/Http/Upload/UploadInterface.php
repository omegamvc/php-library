<?php

namespace System\Http\Upload;

/**
 * This class use for upload file to server using move_uploaded_file() function,
 * make with easy use and manifest, every one can use and modify this class to improve performance.
 *
 * @author sonypradana@gmail.com
 */
interface UploadInterface
{
    /**
     * Set file name (without extension).
     * File name will convert to allow string url.
     *
     * @param string $fileName File name (without extension)
     * @return AbstractUpload
     */
    public function setFileName(string $fileName): AbstractUpload;

    /**
     * File to save/upload location (server folder),
     * Warning:: not creat new folder if location not exist.
     *
     * @param string $folderLocation Upload file to save location
     * @return AbstractUpload
     */
    public function setFolderLocation(string $folderLocation): AbstractUpload;

    /**
     * List allow file extension to upload.
     *
     * @param array<int, string> $extensions list extension file
     * @return AbstractUpload
     */
    public function setFileTypes(array $extensions): AbstractUpload;

    /**
     * List allow file mime type to upload.
     *
     * @param array<int, string> $mimes list mime type file
     * @return AbstractUpload
     */
    public function setMimeTypes(array $mimes): AbstractUpload;

    /**
     * file size to upload (in byte).
     *
     * @param int $byte file size upload
     * @return AbstractUpload
     */
    public function setMaxFileSize(int $byte): AbstractUpload;

    /**
     * If true, upload determinate using `copy` instance of `move_uploaded_file`.
     *
     * @param bool $markUploadTest true use copy file
     * @return AbstractUpload
     */
    public function markTest(bool $markUploadTest): AbstractUpload;

    /**
     * File Upload status.
     *
     * @return bool True on file upload success
     */
    public function success(): bool;

    /**
     * Error message file upload status.
     *
     * @return string Give url file location
     */
    public function getError(): string;

    /**
     * Get uploaded file types.
     *
     * @return array<int, string>
     */
    public function getFileTypes(): array;

    /**
     * Helper to delete file if needed.
     *
     * @param string $url
     * @return bool True on success deleted file
     */
    public function delete(string $url): bool;

    /**
     * Helper to creat new folder if needed.
     *
     * @param string $path
     * @return bool True on success created folder
     */
    public function creatFolder(string $path): bool;
}
