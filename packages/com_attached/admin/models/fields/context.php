<?php
/**
 * @package     Attached
 * @subpackage  com_attached
 *
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @copyright   Copyright (C) 2013 CTIS IT Services. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Context Field class for the Attached.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class JFormFieldContext extends JFormFieldList
{
	/**
	 * The form field context.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $type = 'Context';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.2
	 */
	protected function getOptions()
	{
		// Initialiase variables.
		$options = FilesHelper::getContextOptions();

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
