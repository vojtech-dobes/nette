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
 * Single line text input control.
 *
 * @author     David Grudl
 */
class TextInput extends TextBase
{
	/** @var int */
	public $minValue;

	/** @var int */
	public $maxValue;



	/**
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($maxLength = NULL)
	{
		parent::__construct();
		$this->filters[] = callback($this, 'sanitize');
		$this->maxLength = $maxLength;
		$this->value = '';
	}



	/**
	 * Filter: removes unnecessary whitespace and shortens value to control's max length.
	 * @return string
	 */
	public function sanitize($value)
	{
		if ($this->maxLength && Nette\Utils\Strings::length($value) > $this->maxLength) {
			$value = iconv_substr($value, 0, $this->maxLength, 'UTF-8');
		}
		return Nette\Utils\Strings::trim(strtr($value, "\r\n", '  '));
	}



	/** @deprecated */
	public function setType($type)
	{
		//trigger_error(__METHOD__ . '() is deprecated; use getRenderer()->setType() instead.', E_USER_WARNING);
		$this->getRenderer()->setType($type);
		return $this;
	}

}
