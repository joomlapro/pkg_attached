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
defined('_JEXEC') or die;

/**
 * File JSON controller for Attached Component.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.3
 */
class AttachedControllerFile extends JControllerLegacy
{
	/**
	 * Method to delete file via AJAX using a model.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function deleteFileAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get the application.
		$app = JFactory::getApplication();

		// Get items to remove from the request.
		$cid = $app->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel('File', 'AttachedModel');

		// Make sure the item ids are integers
		jimport('joomla.utilities.arrayhelper');
		JArrayHelper::toInteger($cid);

		// Delete the file.
		$return = $model->delete($cid);

		if ($return)
		{
			echo "1";
		}

		// Close the application.
		$app->close();
	}
}
