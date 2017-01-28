<?php
	namespace KrameWork\Storage;

	require_once("BaseFile.php");

	class JSONFile extends BaseFile
	{
		/**
		 * JSONFile constructor.
		 * @param string $file Initial file to load.
		 * @param bool $useContainer Loaded/inserted data will be contained using a KeyValueContainer.
		 * @param bool $autoLoad If true and file is provided, will attempt to read on construct.
		 * @throws KrameWorkFileException
		 */
		public function __construct(string $file = null, bool $useContainer = true, bool $autoLoad = true) {
			$this->useContainer = $useContainer;
			if ($useContainer && $file === null)
				$this->data = new \ArrayObject();

			parent::__construct($file, $autoLoad);
		}

		/**
		 * Get a value from the underlying data object.
		 * @param string $key
		 * @return mixed|null
		 * @throws KrameWorkFileException
		 */
		public function __get($key) {
			$this->verifyDataObject();
			return $this->data[$key] ?? null;
		}

		/**
		 * Set a value of the underlying data object.
		 * @param string $key
		 * @param mixed $value
		 * @throws KrameWorkFileException
		 */
		public function __set($key, $value) {
			$this->verifyDataObject();
			$this->data[$key] = $value;
		}

		/**
		 * Unset a value from the underlying data object.
		 * @param $key
		 */
		public function __unset($key) {
			$this->verifyDataObject();
			unset($this->data[$key]);
		}

		/**
		 * Get the raw data contained in this JSON wrapper.
		 * @return KeyValueContainer|mixed
		 */
		public function getRawData() {
			return $this->data;
		}

		/**
		 * Set the raw data contained in this JSON wrapper.
		 * @param $data
		 */
		public function setRawData($data) {
			$this->data = $data;
		}

		/**
		 * Set the recursion depth for file reading.
		 * @param int $depth
		 */
		public function setRecursionDepth(int $depth) {
			$this->depth = $depth;
		}

		/**
		 * Set if this file should read objects as associative arrays.
		 * @param bool $assoc
		 */
		public function setAssociative(bool $assoc) {
			$this->assoc = $assoc;
		}

		/**
		 * Set the JSON options bit-mask.
		 * @param int $mask
		 */
		public function setOptions(int $mask) {
			$this->options = $mask;
		}

		/**
		 * Populate the file object using loaded raw data.
		 * Called directly after a successful read() call.
		 * @param string $data Raw data.
		 * @throws KrameWorkFileException
		 */
		public function parse(string $data) {
			$decoded = json_decode($data, $this->assoc, $this->depth, $this->options);
			if ($decoded === null)
				$this->throwJSONError();

			$this->data = $this->useContainer ? new \ArrayObject($decoded) : $decoded;
		}

		/**
		 * Compile the populated data into a writable string.
		 * Called during a write() call for file-writing.
		 * @return string Compiled data.
		 * @throws KrameWorkFileException
		 */
		public function compile(): string {
			$encoded = json_encode($this->data);
			if ($encoded === null)
				$this->throwJSONError();

			return $encoded;
		}

		/**
		 * Throw the latest JSON error as an exception.
		 * @throws KrameWorkFileException
		 */
		private function throwJSONError() {
			throw new KrameWorkFileException("JSON error: " . json_last_error_msg());
		}

		/**
		 * Throw an exception if the internal data object is not initiated.
		 * @throws KrameWorkFileException
		 */
		private function verifyDataObject() {
			if ($this->data === null)
				throw new KrameWorkFileException("Attempt to invoke value on a non-initiated JSON file.");
		}

		/**
		 * @var mixed Internal data object.
		 */
		protected $data;

		/**
		 * @var bool Store data inside a KeyValueContainer.
		 */
		protected $useContainer;

		/**
		 * @var int Recursion depth.
		 */
		protected $depth = 512;

		/**
		 * @var bool Convert objects into associative arrays.
		 */
		protected $assoc = false;

		/**
		 * @var int Bit-mask for JSON encoding options.
		 */
		protected $options = 0;
	}