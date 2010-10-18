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
class DefaultSelectControlRenderer extends DefaultLabeledControlRenderer
{
	/** @var string */
	private $firstItem;

		

	public function __construct(IControl $control, $caption = NULL, $size = NULL)
	{
		parent::__construct($control, $caption);
		$this->element->size = $size > 1 ? (int) $size : NULL;
	}



	/**
	 * Sets first ignored item.
	 * @param  string
	 * @return DefaultSelectControlRenderer  provides a fluent interface
	 */
	public function setFirstItem($item)
	{
		$this->firstItem = $item;
		return $this;
	}
	
	
	
	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$element = parent::getControl($caption);
		$element->setName('select');
		$element->multiple = $this->control instanceof Controls\MultiSelectBox;
		$items = $this->control->getItems();
		$useKeys = $this->control->areKeysUsed();
		if ($this->firstItem !== NULL) {
			$items = array('' => $this->firstItem) + $items;
			$element->data['nette-empty-value'] = $useKeys ? '' : $this->firstItem;
		}
		$selected = $this->control->getValue();
		$selected = is_array($selected) ? array_flip($selected) : array($selected => TRUE);
		$option = Html::el('option');

		foreach ($items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
				$dest = $element;

			} else {
				$dest = $element->create('optgroup')->label($key);
			}

			foreach ($value as $key2 => $value2) {
				if ($value2 instanceof Html) {
					$dest->add((string) $value2->selected(isset($selected[$key2])));

				} else {
					$key2 = $useKeys ? $key2 : $value2;
					$value2 = $this->translate((string) $value2);
					$dest->add((string) $option->value($key2 === $value2 ? NULL : $key2)->selected(isset($selected[$key2]))->setText($value2));
				}
			}
		}
		return $element;
	}

}
