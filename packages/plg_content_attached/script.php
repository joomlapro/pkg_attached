<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Attached
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2014 AtomTech, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Script file of Attached Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.3
 */
class PlgContentAttachedInstallerScript
{
	/**
	 * Called after any type of action.
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install).
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function postflight($route, JAdapterInstance $adapter)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = ' . $db->quote('1'))
			->where($db->quoteName('name') . ' = ' . $db->quote($adapter->get('name')));

		// Set the query and execute the update.
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Called on installation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function install(JAdapterInstance $adapter)
	{
		// Set the redirect location.
		$adapter->getParent()->setRedirectURL('index.php?option=com_plugins&view=plugins&filter_folder=content');
	}

	/**
	 * Called on uninstallation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		echo '<p>' . JText::_('PLG_CONTENT_ATTACHED_UNINSTALL_TEXT') . '</p>';
	}
}
