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
 * Select box control that allows single item selection.
 *
 * @author     David Grudl
 *
 * @property-read mixed $rawValue
 * @property   array $items
 * @property-read mixed $selectedItem
 * @property-read bool $firstSkipped
 */
class SelectBox extends BaseControl
{
	/** @var array */
	private $items = array();

	/** @var array */
	protected $allowed = array();

	/** @var bool */
	private $skipFirst = FALSE;

	/** @var bool */
	private $useKeys = TRUE;



	/**
	 * @param  array   items from which to choose
	 */
	public function __construct(array $items = NULL)
	{
		parent::__construct();
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	/**
	 * Returns selected item key.
	 * @return mixed
	 */
	public function getValue()
	{
		$allowed = $this->allowed;
		if ($this->skipFirst) {
			$allowed = array_slice($allowed, 1, count($allowed), TRUE);
		}

		return is_scalar($this->value) && isset($allowed[$this->value]) ? $this->value : NULL;
	}



	/**
	 * Returns selected item key (not checked).
	 * @return mixed
	 */
	public function getRawValue()
	{
		return is_scalar($this->value) ? $this->value : NULL;
	}



	/**
	 * Has been any item selected?
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->getValue();
		return is_array($value) ? count($value) > 0 : $value !== NULL;
	}



	/**
	 * Ignores the first item in select box.
	 * @param  string
	 * @return SelectBox  provides a fluent interface
	 */
	public function skipFirst($item = NULL)
	{
		if (is_bool($item)) {
			$this->skipFirst = $item;
		} else {
			$this->skipFirst = TRUE;
			if ($item !== NULL) {
				$this->items = array('' => $item) + $this->items;
				$this->allowed = array('' => '') + $this->allowed;
			}
		}
		return $this;
	}



	/**
	 * Is first item in select box ignored?
	 * @return bool
	 */
	final public function isFirstSkipped()
	{
		return $this->skipFirst;
	}



	/**
	 * Are the keys used?
	 * @return bool
	 */
	final public function areKeysUsed()
	{
		return $this->useKeys;
	}



	/**
	 * Sets items from which to choose.
	 * @param  array
	 * @return SelectBox  provides a fluent interface
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		$this->items = $items;
		$this->allowed = array();
		$this->useKeys = (bool) $useKeys;

		foreach ($items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
			}

			foreach ($value as $key2 => $value2) {
				if (!$this->useKeys) {
					if (!is_scalar($value2)) {
						throw new Nette\InvalidArgumentException("All items must be scalar.");
					}
					$key2 = $value2;
				}

				if (isset($this->allowed[$key2])) {
					throw new Nette\InvalidArgumentException("Items contain duplication for key '$key2'.");
				}

				$this->allowed[$key2] = $value2;
			}
		}
		return $this;
	}



	/**
	 * Returns items from which to choose.
	 * @return array
	 */
	final public function getItems()
	{
		return $this->items;
	}



	/**
	 * Returns selected value.
	 * @return string
	 */
	public function getSelectedItem()
	{
		if (!$this->useKeys) {
			return $this->getValue();

		} else {
			$value = $this->getValue();
			return $value === NULL ? NULL : $this->allowed[$value];
		}
	}

}
