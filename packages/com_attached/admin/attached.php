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

// Load the tabstate behavior script.
JHtml::_('behavior.tabstate');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_attached'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Register dependent classes.
JLoader::register('AttachedHelper', __DIR__ . '/helpers/attached.php');
JLoader::register('FilesHelper', __DIR__ . '/helpers/files.php');

// Execute the task.
$controller = JControllerLegacy::getInstance('Attached');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
