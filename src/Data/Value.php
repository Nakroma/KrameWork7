<?php
	/**
	 * Created by PhpStorm.
	 * User: MNI
	 * Date: 12.02.2017
	 * Time: 22.07
	 */
	namespace KrameWork\Data;

	/**
	 * Class Value
	 * A generic value
	 */
	class Value
	{
		public function __construct($value)
		{
			$this->value = $value;
		}

		public function Real()
		{
			return $this->value;
		}

		public function JSON()
		{
			return $this->value;
		}

		public function Compare($to)
		{
			return strnatcasecmp((string)$this->value, (string)$to);
		}

		public function __toString()
		{
			return (string)$this->value;
		}

		protected $value;
	}