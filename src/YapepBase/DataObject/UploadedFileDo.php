<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   DataObject
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\DataObject;
use YapepBase\Exception\ParameterException;

/**
 * Data class for holding uploaded file data.
 *
 * @package    YapepBase
 * @subpackage DataObject
 */
class UploadedFileDo {

	/**
	 * The uploaded file names.
	 *
	 * @var array
	 */
	protected $filenames;

	/**
	 * The uploaded file sizes in bytes.
	 *
	 * @var array
	 */
	protected $sizes;

	/**
	 * The temporary files with full path.
	 *
	 * @var array
	 */
	protected $temporaryFiles;

	/**
	 * The file MIME types.
	 *
	 * @var array
	 */
	protected $types;

	/**
	 * The upload error codes.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Constructor
	 *
	 * @param array $data   The file data with the same structure as the default PHP $_FILES array.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the provided data array doesn't have the required structure.
	 */
	public function __construct(array $data) {
		if (!isset($data['name']) || empty($data['tmp_name']) || !isset($data['size']) || !isset($data['error'])) {
			throw new ParameterException('Invalid array provided. Some required fields are missing');
		}

		$this->filenames      = array_values((array)$data['name']);
		$this->sizes          = array_values((array)$data['size']);
		$this->temporaryFiles = array_values((array)$data['tmp_name']);
		$this->types          = isset($data['type']) ? array_values((array)$data['type']) : array();
		$this->errors         = array_values((array)$data['error']);

		$nameCount = count($this->filenames);
		if ($nameCount != count($this->sizes) || $nameCount != count($this->types)) {
			// The sizes don't match, so it's an invalid data array
			throw new ParameterException('Invalid array provided. The size of the keys do not match');
		}
	}

	/**
	 * Returns the number of files stored in this object.
	 *
	 * @return int
	 */
	public function getFileCount() {
		return count($this->filenames);
	}

	/**
	 * The original uploaded filename as provided by the client.
	 *
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
	 */
	public function getFilename($index = 0) {
		if (!isset($this->filenames[$index])) {
			throw new ParameterException('Invalid index: ' . $index);
		}
		return (string)$this->filenames[$index];
	}

	/**
	 * Returns the extension for the uploaded file based on the original filename or FALSE if it can't determine this.
	 *
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @return string|bool
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
	 */
	public function getExtension($index = 0) {
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
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @return int
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
	 */
	public function getSize($index = 0) {
		if (!isset($this->sizes[$index])) {
			throw new ParameterException('Invalid index: ' . $index);
		}
		return (int)$this->sizes[$index];
	}

	/**
	 * Returns the full path of the temporary file.
	 *
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
	 */
	public function getTemporaryFile($index = 0) {
		if (!isset($this->temporaryFiles[$index])) {
			throw new ParameterException('Invalid index: ' . $index);
		}
		return (string)$this->temporaryFiles[$index];
	}

	/**
	 * Returns the mime type of the uploaded file as provided by the client or NULL if it's not provided.
	 *
	 * Do not trust this data, as it is provided by the client and not verified to be correct!
	 * This method will not throw an exception if an invalid index is provided, instead it will return NULL.
	 *
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @return null
	 */
	public function getMimeType($index = 0) {
		if (!isset($this->types[$index])) {
			return null;
		}
		return $this->types[$index];
	}

	/**
	 * Returns the content of the uploaded file.
	 *
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
	 *
	 * @return string
	 */
	public function getFileContent($index = 0) {
		return file_get_contents($this->getTemporaryFile($index));
	}

	/**
	 * Returns the upload error code for this upload.
	 *
	 * @param int $index   The file index if an array of files are uploaded. Indexed from 0.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the index does not exist.
	 *
	 * @return int {@uses \UPLOAD_ERR_*}
	 */
	public function getError($index = 0) {
		if (!isset($this->temporaryFiles[$index])) {
			throw new ParameterException('Invalid index: ' . $index);
		}
		return $this->errors[$index];
	}

	/**
	 * Returns TRUE, if the DO contains data for more then 1 file.
	 *
	 * @return bool
	 */
	public function isMultiUpload() {
		return $this->getFileCount() > 1;
	}

	/**
	 * Returns TRUE if the upload failed because of an error.
	 *
	 * @return bool
	 */
	public function hasError() {
		foreach ($this->errors as $errorCode) {
			if ($errorCode !== UPLOAD_ERR_OK) {
				return true;
			}
		}
		return false;
	}
}