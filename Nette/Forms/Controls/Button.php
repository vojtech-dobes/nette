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
 * Button control.
 *
 * @author     David Grudl
 *
 * @property   mixed $validationScope
 * @property-read bool $submittedBy
 */
class Button extends BaseControl implements ISubmitterControl
{
	/** @var array of function(Button $sender); Occurs when the button is clicked and form is successfully validated */
	public $onClick;

	/** @var array of function(Button $sender); Occurs when the button is clicked and form is not validated */
	public $onInvalidClick;

	/** @var mixed */
	private $validationScope = TRUE;



	/**
	 * Sets 'pressed' indicator.
	 * @param  bool
	 * @return Button  provides a fluent interface
	 */
	public function setValue($value)
	{
		$this->value = is_scalar($value) && (bool) $value;
		$form = $this->getForm();
		if ($this->value || !is_object($form->isSubmitted())) {
			$this->value = TRUE;
			$form->setSubmittedBy($this);
		}
		return $this;
	}



	/**
	 * Tells if the form was submitted by this button.
	 * @return bool
	 */
	public function isSubmittedBy()
	{
		return $this->getForm()->isSubmitted() === $this;
	}



	/**
	 * Sets the validation scope. Clicking the button validates only the controls within the specified scope.
	 * @param  mixed
	 * @return Button  provides a fluent interface
	 */
	public function setValidationScope($scope)
	{
		// TODO: implement groups
		$this->validationScope = (bool) $scope;
		return $this;
	}



	/**
	 * Gets the validation scope.
	 * @return mixed
	 */
	final public function getValidationScope()
	{
		return $this->validationScope;
	}



	/**
	 * Fires click event.
	 * @return void
	 */
	public function click()
	{
		$this->onClick($this);
	}



	/**
	 * Submitted validator: has been button pressed?
	 * @param  ISubmitterControl
	 * @return bool
	 */
	public static function validateSubmitted(ISubmitterControl $control)
	{
		return $control->isSubmittedBy();
	}

	
	
	/**
	 * Returns name of control within a Form & INamingContainer scope.
	 * @return string
	 */
	public function getHtmlName()
	{
		$name = parent::getHtmlName();
		return strpos($name, '[') === FALSE ? $name : $name . '[]'; // TODO
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$path = $this->getHtmlName(); // img_x or img['x']
		$path = explode('[', strtr(str_replace(']', '', strpos($path, '[') === FALSE ? $path . '.x' : substr($path, 0, -2)), '.', '_')); // TODO
		$this->setValue(Nette\ArrayUtils::get($this->getForm()->getHttpData(), $path) !== NULL);
	}
	
}
