<?php
	/**
	 * Created by PhpStorm.
	 * User: MNI
	 * Date: 12.02.2017
	 * Time: 22.08
	 */
	namespace KrameWork\Data;

	/**
	 * Class DateValue
	 * A date value
	 */
	class DateValue extends Value
	{
		public function __construct($value)
		{
			parent::__construct(strtotime($value));
		}

		public function JSON()
		{
			// Use ISO 8601 format to support moment.js client side
			return date($this->value, 'c');
		}

		public function Compare($to)
		{
			$toValue = ($to instanceof DateValue) ? $to->Real() : 0;
			return $this->value - $toValue;
		}

		public function __toString()
		{
			return date('d.m.Y', $this->value);
		}
	}