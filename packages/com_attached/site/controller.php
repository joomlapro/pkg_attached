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
defined('_JEXEC') or die;

/**
 * Attached Component Controller.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class AttachedController extends JControllerLegacy
{
	/**
	 * Method to register the download.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function download()
	{
		// Load the backend helper.
		require_once JPATH_ADMINISTRATOR . '/components/com_attached/helpers/files.php';

		// Initialiase variables.
		$app = JFactory::getApplication();
		$pk  = $app->input->getInt('id');

		// Include dependancies.
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_attached/models', 'AttachedModel');

		// Get an instance of the generic file model.
		$model = JModelLegacy::getInstance('File', 'AttachedModel', array('ignore_request' => true));
		$model->download($pk);

		$item = $model->getItem($pk);

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $item->file['type']);
		header('Content-Type: application/force-download');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment; filename=' . $item->file['name']);
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Content-Length: ' . $item->file['size']);

		readfile(JPATH_ROOT . '/uploads/' . str_replace('.', '/', $item->context) . '/' . $item->file['name']);

		// Close the application.
		$app->close();
	}
}
