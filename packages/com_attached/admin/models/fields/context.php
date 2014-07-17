<?php
/**
 * @package     Attached
 * @subpackage  com_attached
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2014 AtomTech, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Context Field class for the Attached.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.3
 */
class JFormFieldContext extends JFormFieldList
{
	/**
	 * The form field context.
	 *
	 * @var     string
	 * @since   3.3
	 */
	protected $type = 'Context';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.3
	 */
	protected function getOptions()
	{
		// Initialiase variables.
		$options = FilesHelper::getContextOptions();

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
