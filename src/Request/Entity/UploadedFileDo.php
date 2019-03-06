<?php
declare(strict_types=1);

namespace YapepBase\Request\Entity;

use YapepBase\Application;
use YapepBase\Exception\ParameterException;

/**
 * Data class for holding uploaded file data.
 */
class UploadedFileDo
{
    /** @var array */
    protected $filenames = [];

    /** @var array */
    protected $sizesInByte = [];

    /** @var array */
    protected $temporaryFilePaths = [];

    /** @var array */
    protected $mimeTypes = [];

    /** @var array */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param array $data The file data with the same structure as the default PHP $_FILES array.
     *
     * @throws \YapepBase\Exception\ParameterException   If the provided data array doesn't have the required structure.
     */
    public function __construct(array $data)
    {
        if (!isset($data['name']) || !isset($data['error']) || !isset($data['size']) || (empty($data['tmp_name']) && $data['error'] == UPLOAD_ERR_OK)) {
            throw new ParameterException('Invalid array provided. Some required fields are missing');
        }

        $this->filenames          = array_values((array)$data['name']);
        $this->sizesInByte        = array_values((array)$data['size']);
        $this->temporaryFilePaths = array_values((array)$data['tmp_name']);
        $this->mimeTypes          = isset($data['type']) ? array_values((array)$data['type']) : [];
        $this->errors             = array_values((array)$data['error']);

        $nameCount = count($this->filenames);
        if ($nameCount != count($this->sizesInByte) || $nameCount != count($this->mimeTypes)) {
            // The sizes don't match, so it's an invalid data array
            throw new ParameterException('Invalid array provided. The size of the keys do not match');
        }

        $fileHandler = Application::getInstance()->getDiContainer()->getFileHandler();

        // If the filesize is smaller than 0, we have a file that's bigger than 2GB, repopulate the file sizes.
        foreach ($this->sizesInByte as $index => $size) {
            if ($size < 0 && $fileHandler->checkIsPathExists($this->temporaryFilePaths[$index])) {
                $this->sizesInByte[$index] = $fileHandler->getSize($this->temporaryFilePaths[$index]);
            }
        }
    }

    /**
     * Returns the number of files stored in this object.
     *
     * @return int
     */
    public function getFileCount()
    {
        return count($this->filenames);
    }

    /**
     * The original uploaded filename as provided by the client.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @return string
     *
     * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
     */
    public function getFilename($index = 0)
    {
        if (!isset($this->filenames[$index])) {
            throw new ParameterException('Invalid index: ' . $index);
        }
        return (string)$this->filenames[$index];
    }

    /**
     * Returns the extension for the uploaded file based on the original filename or FALSE if it can't determine this.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @return string|bool
     *
     * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
     */
    public function getExtension($index = 0)
    {
        $filename = $this->getFilename($index);
        if (preg_match('/\.([a-zA-Z0-9]+)$/', $filename, $matches)) {
            return $matches[1];
        } else {
            return false;
        }
    }

    /**
     * Returns the size of the uploaded file in bytes.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @return int
     *
     * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
     */
    public function getSize($index = 0)
    {
        if (!isset($this->sizesInByte[$index])) {
            throw new ParameterException('Invalid index: ' . $index);
        }
        return (int)$this->sizesInByte[$index];
    }

    /**
     * Returns the full path of the temporary file.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @return string
     *
     * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
     */
    public function getTemporaryFile($index = 0)
    {
        if (!isset($this->temporaryFilePaths[$index])) {
            throw new ParameterException('Invalid index: ' . $index);
        }
        return (string)$this->temporaryFilePaths[$index];
    }

    /**
     * Returns the mime type of the uploaded file as provided by the client or NULL if it's not provided.
     *
     * Do not trust this data, as it is provided by the client and not verified to be correct!
     * This method will not throw an exception if an invalid index is provided, instead it will return NULL.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @return null
     */
    public function getMimeType($index = 0)
    {
        if (!isset($this->mimeTypes[$index])) {
            return null;
        }
        return $this->mimeTypes[$index];
    }

    /**
     * Returns the content of the uploaded file.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
     *
     * @return string
     */
    public function getFileContent($index = 0)
    {
        return file_get_contents($this->getTemporaryFile($index));
    }

    /**
     * Returns the upload error code for this upload.
     *
     * @param int $index The file index if an array of files are uploaded. Indexed from 0.
     *
     * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
     *
     * @return int {@uses \UPLOAD_ERR_*}
     */
    public function getError($index = 0)
    {
        if (!isset($this->temporaryFilePaths[$index])) {
            throw new ParameterException('Invalid index: ' . $index);
        }
        return $this->errors[$index];
    }

    /**
     * Returns TRUE, if the DO contains data for more then 1 file.
     *
     * @return bool
     */
    public function isMultiUpload()
    {
        return $this->getFileCount() > 1;
    }

    /**
     * Returns TRUE if the upload failed because of an error.
     *
     * @return bool
     */
    public function hasError()
    {
        foreach ($this->errors as $errorCode) {
            if ($errorCode !== UPLOAD_ERR_OK) {
                return true;
            }
        }
        return false;
    }
}
