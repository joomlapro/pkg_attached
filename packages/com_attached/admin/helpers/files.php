<?php
/**
 * @package     Attached
 * @subpackage  com_attached
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Files helper.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class FilesHelper
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $id         The item ID.
	 * @param   string   $assetName  The asset name.
	 *
	 * @return  JObject  A JObject containing the allowed actions.
	 *
	 * @since   3.2
	 */
	public static function getActions($id = 0, $assetName = '')
	{
		// Initialiase variables.
		$user   = JFactory::getUser();
		$result = new JObject;
		$path   = JPATH_ADMINISTRATOR . '/components/' . $assetName . '/access.xml';

		if (empty($id))
		{
			$section = 'component';
		}
		else
		{
			$section = 'file';
			$assetName .= '.file.' . (int) $id;
		}

		$actions = JAccess::getActionsFromFile($path, "/access/section[@name='" . $section . "']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the contexts.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @return  3.2
	 */
	public static function getContextOptions()
	{
		// Build the filter options.
		$options = array();

		// Load the attached plugin group.
		JPluginHelper::importPlugin('attached');

		// Get the event dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Trigger the onGetConfig event.
		$result = $dispatcher->trigger('onGetConfig', array($config = array()));

		foreach ($result as $item)
		{
			$options[] = JHtml::_('select.option', $item['context'], $item['text']);
		}

		return $options;
	}
}
