<?php
	namespace KrameWork\Runtime\ErrorFormatters;

	use KrameWork\Reporting\HTMLReport\HTMLReportTemplate;
	use KrameWork\Runtime\ErrorReports\ErrorReport;
	use KrameWork\Runtime\ErrorReports\IErrorReport;
	use KrameWork\Runtime\ErrorTypes\IError;

	require_once(__DIR__ . '/../../Reporting/HTMLReport/HTMLReportTemplate.php');

	class HTMLErrorFormatter implements IErrorFormatter
	{
		/**
		 * HTMLErrorFormatter constructor.
		 *
		 * @api __construct
		 * @param string|null $templatePath Path to HTML template.
		 */
		public function __construct($templatePath = null) {
			$this->templatePath = $templatePath;
		}

		/**
		 * Called just before this report is used.
		 *
		 * @api beginReport
		 */
		public function beginReport() {
			$this->data = [];
			$this->basicData = [];
			$this->trace = [];
		}

		/**
		 * Format an error and add it to the report.
		 *
		 * @api handleError
		 * @param IError $error Error which occurred.
		 */
		public function reportError(IError $error) {
			$this->error = $error;
			$this->trace = $error->getTrace();
			$this->basicData = [
				'timestamp' => time(),
				'name' => $error->getName(),
				'file' => $error->getFile(),
				'line' => $error->getLine(),
				'occurred' => date(DATE_RFC822),
				'prefix' => $error->getPrefix(),
				'message' => $error->getMessage()
			];
		}

		/**
		 * Format an array and add it to the report.
		 *
		 * @api formatArray
		 * @param string $name Name for the array.
		 * @param array $arr Array of data.
		 */
		public function reportArray(string $name, array $arr) {
			$this->data[$name] = $arr;
		}

		/**
		 * Format a data string and add it to the report.
		 *
		 * @api reportString
		 * @param string $name Name of the data string.
		 * @param string $str Data string.
		 */
		public function reportString(string $name, string $str) {
			$this->data[$name] = $str;
		}

		/**
		 * Generate a report.
		 *
		 * @api generate
		 * @return IErrorReport
		 */
		public function generate():IErrorReport {
			$report = new HTMLReportTemplate($this->getTemplate());

			// Handle basic data.
			foreach ($this->basicData as $key => $value)
				$report->$key = $value;

			// Handle stacktrace.
			$traceSection = $report->getSection('TRACE_FRAME');
			if ($traceSection->isValid()) {
				$index = 0;

				foreach ($this->trace as $traceFrame) {
					$args = [];
					foreach ($traceFrame['args'] ?? [] as $key => $arg)
						$args[$key] = $this->getVariableString($arg);

					$frame = $traceSection->createFrame();
					$frame->index = $index++;
					$frame->file = $traceFrame['file'] ?? 'interpreter';
					$frame->line = $traceFrame['line'] ?? '?';
					$frame->class = $traceFrame['class'] ?? '';
					$frame->type = $traceFrame['type'] ?? '';
					$frame->function = $traceFrame['function'] ?? '';
					$frame->args = implode(', ', $args);
				}
			}

			// Handle data sections.
			$stringSection = $report->getSection('DATA_SET_STRING');
			$arraySection = $report->getSection('DATA_SET_ARRAY');

			foreach ($this->data as $name => $data) {
				if (is_array($data)) {
					if (!$arraySection->isValid())
						continue;

					$frame = $arraySection->createFrame();
					$frame->name = $name;

					$frameSection = $frame->getSection('DATA_SET_FRAME');
					if (count($data)) {
						foreach ($data as $nodeKey => $nodeValue) {
							$nodeFrame = $frameSection->createFrame();
							$nodeFrame->name = $nodeKey;
							$nodeFrame->data = $this->getVariableString($nodeValue);
						}
					} else {
						$nodeFrame = $frameSection->createFrame();
						$nodeFrame->name = '';
						$nodeFrame->data = 'No data to display.';
					}
				} else {
					if (!$stringSection->isValid())
						continue;

					$frame = $stringSection->createFrame();
					$frame->name = $name;
					$frame->data = $this->getVariableString($data);
				}
			}

			return new ErrorReport($this->error, 'text/html; charset=utf-8', '.html', $report);
		}

		private function getVariableString($var):string {
			$type = gettype($var);
			if ($type == 'object') {
				$type = get_class($var);
				if (!method_exists($var, '__toString'))
					$var = $type . ' instance';

			} elseif ($type == 'string') {
				$length = \strlen($var);
				$var = "({$length}) \"{$var}\"";
			} elseif ($type == 'array') {
				$var = count($var) . ' items';
			}

			return "({$type}) {$var}";
		}

		/**
		 * Get the template for this report.
		 *
		 * @internal
		 * @return string
		 */
		private function getTemplate():string {
			if ($this->template !== null)
				return $this->template;

			if ($this->templatePath !== null && $this->loadTemplateFile($this->templatePath))
				return $this->template;

			if ($this->loadTemplateFile(__DIR__ . '/../../../templates/error_report.html'))
				return $this->template;

			return '';
		}

		/**
		 * Attempt to load template data from a file.
		 *
		 * @internal
		 * @param string $file File to load the template from.
		 * @return bool
		 */
		private function loadTemplateFile(string $file):bool {
			if (file_exists($file) && is_file($file)) {
				$data = file_get_contents($file);
				if ($data !== false) {
					$this->template = $data;
					return true;
				}
			}
			return false;
		}

		/**
		 * @var IError
		 */
		protected $error;

		/**
		 * @var array
		 */
		protected $data;

		/**
		 * @var array
		 */
		protected $basicData;

		/**
		 * @var array
		 */
		protected $trace;

		/**
		 * @var string
		 */
		protected $template;

		/**
		 * @var string
		 */
		protected $templatePath;
	}