<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.Attached
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Editor Attached button.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class PlgButtonAttached extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var     boolean
	 * @since   3.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add.
	 *
	 * @return  array A four element array of (attached_id, attached_title, category_id, object).
	 *
	 * @since   3.2
	 */
	public function onDisplay($name)
	{
		// Get the application.
		$app = JFactory::getApplication();

		// Detecting Active Variables
		$option = $app->input->getCmd('option', '');
		$view   = $app->input->getCmd('view', '');
		$cid    = $app->input->getInt('id', 0);

		// Load the attached plugin group.
		JPluginHelper::importPlugin('attached');

		// Get the event dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Trigger the onGetConfig event.
		$results = $dispatcher->trigger('onGetConfig', array($config = array()));

		$context = array();

		foreach ($results as $result)
		{
			$context[] = $result['context'];
		}

		if (!in_array($option . '.' . $view, $context) || $option == 'com_attached')
		{
			return;
		}

		if (!$cid)
		{
			// Load the tooltip bootstrap script.
			JHtml::_('bootstrap.tooltip');

			$button = new JObject;
			$button->modal    = false;
			$button->class    = 'btn hasTooltip';
			$button->text     = JText::_('PLG_EDITORS-XTD_ATTACHED_BUTTON_ATTACHED');
			$button->name     = 'file-add';
			$button->disabled = true;
			$button->title    = JText::_('PLG_EDITORS-XTD_ATTACHED_SAVE_FIRST');

			return $button;
		}

		// Get the document object.
		JHtml::_('behavior.modal');

		/*
		 * Use the built-in element view to select the file.
		 * Currently uses blank class.
		 */
		$link = 'index.php?option=com_attached&amp;view=file&amp;layout=upload&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

		$link .= '&context=' . $option . '.' . $view;
		$link .= '&cid=' . $cid;

		$button = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = JText::_('PLG_EDITORS-XTD_ATTACHED_BUTTON_ATTACHED');
		$button->name    = 'file-add';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
	}
}
