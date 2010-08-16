<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Forms
 */

namespace Nette\Forms;

use Nette,
	Nette\Utils\Html;



/**
 * Helper that implements the basic control rendering.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 *
 * @property-read mixed $control
 * @property   string $htmlId
 * @property-read array $options
 * @property   Nette\Localization\ITranslator $translator
 * @property-read Nette\Utils\Html $elementPrototype
 * @property   bool $rendered
*/
abstract class DefaultControlRenderer extends Nette\Object
{
	/** @var string */
	public static $idMask = 'frm%s-%s';

	/** @var IControl */
	protected $control;

	/** @var string textual caption or label */
	public $caption;

	/** @var string */
	private $htmlId;

	/** @var Nette\Localization\ITranslator */
	private $translator = TRUE; // means autodetect

	/** @var array user options */
	private $options = array();

	/** @var Nette\Utils\Html  control element template */
	protected $element;



	/**
	 * @param  string  caption
	 */
	public function __construct(IControl $control, $caption = NULL)
	{
		$this->control = $control;
		$this->caption = $caption;
		$this->element = Html::el('input', array('type' => NULL, 'class' => NULL)); // move to front
	}



	/**
	 * Changes control's HTML id.
	 * @param  string new ID, or FALSE or NULL
	 * @return Nette\Forms\Controls\BaseControl  provides a fluent interface
	 */
	public function setHtmlId($id)
	{
		$this->htmlId = $id;
		return $this;
	}



	/**
	 * Returns control's HTML id.
	 * @return string
	 */
	public function getHtmlId()
	{
		if ($this->htmlId === FALSE) {
			return NULL;

		} elseif ($this->htmlId === NULL) {
			$this->htmlId = sprintf(self::$idMask, $this->control->getForm()->getName(), $this->control->getHtmlName());
			$this->htmlId = str_replace(array('[]', '[', ']'), array('', '-', ''), $this->htmlId);
		}
		return $this->htmlId;
	}



	/**
	 * Changes control's HTML attribute.
	 * @param  string name
	 * @param  mixed  value
	 * @return Nette\Forms\Controls\BaseControl  provides a fluent interface
	 */
	public function setAttribute($name, $value = TRUE)
	{
		$this->element->$name = $value;
		return $this;
	}



	/**
	 * Sets user-specific option.
	 * Common options:
	 * - 'rendered' - indicate if method getControl() have been called
	 * - 'required' - indicate if ':required' rule has been applied
	 * - 'description' - textual or Html object description (recognized by ConventionalRenderer)
	 *
	 * @param  string key
	 * @param  mixed  value
	 * @return Nette\Forms\Controls\BaseControl  provides a fluent interface
	 */
	public function setOption($key, $value)
	{
		if ($value === NULL) {
			unset($this->options[$key]);

		} else {
			$this->options[$key] = $value;
		}
		return $this;
	}



	/**
	 * Returns user-specific option.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	final public function getOption($key, $default = NULL)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}



	/**
	 * Returns user-specific options.
	 * @return array
	 */
	final public function getOptions()
	{
		return $this->options;
	}



	/********************* translator ****************d*g**/



	/**
	 * Sets translate adapter.
	 * @param  Nette\Localization\ITranslator
	 * @return Nette\Forms\Controls\BaseControl  provides a fluent interface
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
		return $this;
	}



	/**
	 * Returns translate adapter.
	 * @return Nette\Localization\ITranslator|NULL
	 */
	final public function getTranslator()
	{
		if ($this->translator === TRUE) {
			return $this->control->getForm(FALSE) ? $this->control->getForm()->getTranslator() : NULL;
		}
		return $this->translator;
	}



	/**
	 * Returns translated string.
	 * @param  string
	 * @param  int      plural count
	 * @return string
	 */
	public function translate($s, $count = NULL)
	{
		$translator = $this->getTranslator();
		return $translator === NULL || $s == NULL ? $s : $translator->translate($s, $count); // intentionally ==
	}



	/********************* rendering ****************d*g**/



	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$this->setOption('rendered', TRUE);
		$element = clone $this->element;
		$element->name = $this->control->getHtmlName();
		$element->disabled = $this->control->disabled;
		$element->id = $this->getHtmlId();
		$rules = self::exportRules($this->control->rules);
		$rules = substr(json_encode($rules), 1, -1);
		$rules = preg_replace('#"([a-z0-9]+)":#i', '$1:', $rules);
		$rules = preg_replace('#(?<!\\\\)"([^\\\\\',]*)"#i', "'$1'", $rules);
		$element->data['nette-rules'] = $rules ? $rules : NULL;
		return $element;
	}



	/**
	 * Returns control's HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getElementPrototype()
	{
		return $this->element;
	}



	/**
	 * Sets 'rendered' indicator.
	 * @param  bool
	 * @return Nette\Forms\Controls\BaseControl  provides a fluent interface
	 * @deprecated
	 */
	public function setRendered($value = TRUE)
	{
		$this->setOption('rendered', $value);
		return $this;
	}



	/**
	 * Does method getControl() have been called?
	 * @return bool
	 * @deprecated
	 */
	public function isRendered()
	{
		return !empty($this->options['rendered']);
	}



	/**
	 * @return array
	 */
	public static function exportRules($rules)
	{
		$payload = array();
		foreach ($rules as $rule) {
			if (!is_string($rule->operation)) {
				continue;

			} elseif ($rule->type === Rule::VALIDATOR) {
				$item = array('op' => ($rule->isNegative ? '~' : '') . $rule->operation, 'msg' => $rules->formatMessage($rule, FALSE));

			} elseif ($rule->type === Rule::CONDITION) {
				$item = array('op' => ($rule->isNegative ? '~' : '') . $rule->operation, 'rules' => self::exportRules($rule->subRules), 'control' => $rule->control->getHtmlName());
				if ($rule->subRules->getToggles()) {
					$item['toggle'] = $rule->subRules->getToggles();
				}
			}

			if (is_array($rule->arg)) {
				foreach ($rule->arg as $key => $value) {
					$item['arg'][$key] = $value instanceof IControl ? (object) array('control' => $value->getHtmlName()) : $value;
				}
			} elseif ($rule->arg !== NULL) {
				$item['arg'] = $rule->arg instanceof IControl ? (object) array('control' => $rule->arg->getHtmlName()) : $rule->arg;
			}

			$payload[] = $item;
		}
		return $payload;
	}

}
