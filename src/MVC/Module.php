<?php
	/*
	 * Copyright (c) 2017 Kruithne (kruithne@gmail.com)
	 * https://github.com/Kruithne/KrameWork7
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in all
	 * copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	 * SOFTWARE.
	 */

	namespace KrameWork\MVC;

	/**
	 * Interface IModule
	 *
	 * @package KrameWork\MVC
	 * @author Kruithne <kruithne@gmail.com>
	 */
	interface IModule
	{
		/**
		 * Compile this module into a string.
		 *
		 * @api __toString
		 * @return string
		 */
		public function __toString():string;

		/**
		 * Render this module.
		 * Called when module is requested as a string.
		 *
		 * @api render
		 */
		public function render();

		/**
		 * Add another module to this module.
		 *
		 * @api addModule
		 * @param IModule $module Module to add.
		 */
		public function addModule(IModule $module);
	}

	/**
	 * Class Module
	 * Module component for the MVC structure.
	 *
	 * @package KrameWork\MVC
	 * @author Kruithne <kruithne@gmail.com>
	 */
	abstract class Module implements IModule
	{
		/**
		 * Module constructor.
		 *
		 * @api __construct
		 */
		public function __construct() {
			$this->modules = [];
		}

		/**
		 * Compile this module into a string.
		 *
		 * @api __toString
		 * @return string
		 */
		public function __toString(): string {
			$out = '';

			foreach ($this->modules as $module)
				$out .= $module->render();

			$out .= $this->render();
			return $out;
		}

		/**
		 * Add another module to this module.
		 *
		 * @api addModule
		 * @param IModule $module Module to add.
		 */
		public function addModule(IModule $module) {
			$this->modules[] = $module;
		}

		/**
		 * @var IModule[]
		 */
		private $modules;
	}