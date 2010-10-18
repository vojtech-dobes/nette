<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms\Controls;

use Nette;



/**
 * Control which allows multiple choise between multiple items.
 *
 * @author     David Grudl
 */
class MultiChoiceControl extends ChoiceControl
{


	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		return array_intersect($this->getRawValue(), array_keys($this->allowed));
	}



	/**
	 * Returns selected keys (not checked).
	 * @return array
	 */
	public function getRawValue()
	{
		if (is_scalar($this->value)) {
			$value = array($this->value);

		} elseif (!is_array($this->value)) {
			$value = array();

		} else {
			$value = $this->value;
		}

		$res = array();
		foreach ($value as $val) {
			if (is_scalar($val)) {
				$res[] = $val;
			}
		}
		return $res;
	}



	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItem()
	{
		if (!$this->areKeysUsed()) {
			return $this->getValue();

		} else {
			$res = array();
			foreach ($this->getValue() as $value) {
				$res[$value] = $this->allowed[$value];
			}
			return $res;
		}
	}



	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}

}
