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
*/
class DefaultRadioListRenderer extends DefaultLabeledControlRenderer
{
	/** @var Nette\Utils\Html  separator element template */
	protected $separator;

	/** @var Nette\Utils\Html  container element template */
	protected $container;



	/**
	 * @param  string  caption
	 */
	public function __construct(IControl $control, $caption = NULL)
	{
		parent::__construct($control, $caption);
		$this->container = Html::el();
		$this->separator = Html::el('br');
	}



	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$element = parent::getControl($caption);
		$element->type = 'radio';
		$key = $caption;
		$items = $this->control->getItems();

		if ($key === NULL) {
			$container = clone $this->container;
			$separator = (string) $this->separator;

		} elseif (!isset($items[$key])) {
			return NULL;
		}

		$id = $element->id;
		$counter = -1;
		$value = $this->control->getValue() === NULL ? NULL : (string) $this->control->getValue();
		$label = Html::el('label');

		foreach ($items as $k => $val) {
			$counter++;
			if ($key !== NULL && $key != $k) { // intentionally ==
				continue;
			}

			$element->id = $label->for = $id . '-' . $counter;
			$element->checked = (string) $k === $value;
			$element->value = $k;

			if ($val instanceof Html) {
				$label->setHtml($val);
			} else {
				$label->setText($this->translate((string) $val));
			}

			if ($key !== NULL) {
				return (string) $element . (string) $label;
			}

			$container->add((string) $element . (string) $label . $separator);
			unset($element->data['nette-rules']);
			// TODO: separator after last item?
		}

		return $container;
	}



	/**
	 * Returns separator HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getSeparatorPrototype()
	{
		return $this->separator;
	}



	/**
	 * Returns container HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}

}
