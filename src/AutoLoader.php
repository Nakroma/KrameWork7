<?php
	namespace KrameWork;

	use KrameWork\Utils\StringUtil;
	require_once("src/Utils/StringUtil.php");

	class InvalidSourcePathException extends \Exception {}

	/**
	 * Class AutoLoader
	 * @package KrameWork
	 * Handles the automatic loading of files based on class initiation.
	 */
	class AutoLoader
	{
		const RECURSIVE_SOURCING = 0x1;
		const INCLUDE_WORKING_DIRECTORY = 0x2;
		const INCLUDE_KRAMEWORK_DIRECTORY = 0x4;
		const DEFAULT_FLAGS = self::INCLUDE_WORKING_DIRECTORY | self::INCLUDE_KRAMEWORK_DIRECTORY | self::RECURSIVE_SOURCING;

		/**
		 * AutoLoader constructor.
		 * @param string[] $sources List of directories to auto-load from.
		 * @param string[] $extensions Allowed extensions.
		 * @param int $flags Flags to control auto-loading.
		 * @throws InvalidSourcePathException
		 */
		public function __construct(array $sources = null, array $extensions = null, int $flags = self::DEFAULT_FLAGS) {
			$this->enabled = true;
			$this->sources = [];
			$this->extensions = [];

			// Remove any leading periods from extentions.
			foreach ($extensions ?? ["php"] as $ext)
				$this->extensions[] = ltrim($ext, ".");

			// Pre-compute source paths/maps.
			foreach ($sources ?? [] as $source) {
				if (is_array($source) && count($source) == 2) {
					$real = realpath(StringUtil::formatDirectorySlashes($source[1]));

					if ($real === false)
						throw new InvalidSourcePathException("Invalid source path: " . $source[1]);

					$source[1] = $real;

					// Convert namespace separators if needed.
					if (DIRECTORY_SEPARATOR == "/")
						$source[0] = str_replace("\\", DIRECTORY_SEPARATOR, $source[0]);

					$this->sources[] = $source;
				} else if (is_string($source)) {
					$real = realpath(StringUtil::formatDirectorySlashes($source));
					if ($real === false)
						throw new InvalidSourcePathException("Invalid source path: " . $source);

					$this->sources[] = $real;
				}
			}

			if ($flags & self::INCLUDE_KRAMEWORK_DIRECTORY)
				$this->sources["KrameWork"] = dirname(__FILE__);

			if ($flags & self::INCLUDE_WORKING_DIRECTORY)
				$this->sources[] = getcwd();

			// Register this auto-loader instance with PHP.
			spl_autoload_register([$this, 'loadClass']);
		}

		/**
		 * Attempt to load a given class.
		 * @param $className
		 */
		public function loadClass($className) {
			if (!$this->enabled)
				return;

			$className = StringUtil::formatDirectorySlashes($className);
			$queue = $this->sources;

			while (count($queue)) {
				$class = $className;
				$directory = array_shift($queue);
				if (is_array($directory)) {
					list($namespace, $path) = $directory;
					$namespaceLen = strlen($namespace);

					if (strncmp($class, $namespace, $namespaceLen) !== 0)
						continue;

					$class = trim(substr($class, $namespaceLen), DIRECTORY_SEPARATOR);
					$directory = $path;
				}

				foreach ($this->extensions as $ext) {
					$file = $directory . DIRECTORY_SEPARATOR . $class . '.' . $ext;
					if (file_exists($file)) {
						require_once($file);
						return;
					}
				}

				if ($this->flags & self::RECURSIVE_SOURCING) {
					if ($handle = opendir($directory)) {
						while (($entry = readdir($handle)) !== false) {
							if ($entry == "." || $entry == "..")
								continue;

							$path = $directory . DIRECTORY_SEPARATOR . $entry;
							if (is_dir($path))
								array_push($queue, $path);
						}

						closedir($handle);
					}
				}
			}
		}

		/**
		 * Disable this auto-loader.
		 */
		public function disable() {
			$this->enabled = false;
		}

		/**
		 * Enable this auto-loader.
		 */
		public function enable() {
			$this->enabled = true;
		}

		/**
		 * @var string[]
		 */
		private $sources;

		/**
		 * @var string[]
		 */
		private $extensions;

		/**
		 * @var int
		 */
		private $flags;

		/**
		 * @var bool
		 */
		private $enabled;
	}