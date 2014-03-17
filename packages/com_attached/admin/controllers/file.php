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
 * File controller class.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class AttachedControllerFile extends JControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $text_prefix = 'COM_ATTACHED_FILE';

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	protected function allowAdd($data = array())
	{
		// Get the current user object.
		$user = JFactory::getUser();

		return ($user->authorise('core.create', 'com_attached'));
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialiase variables.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_attached.file.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_attached.file.' . $recordId))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;

			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   3.2
	 */
	public function batch($model = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Set the model.
		$model = $this->getModel('File', 'AttachedModel', array());

		// Preset the redirect.
		$this->setRedirect(JRoute::_('index.php?option=com_attached&view=files' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   3.2
	 */
	protected function getRedirectToListAppend()
	{
		// Initialiase variables.
		$append = parent::getRedirectToListAppend();

		return $append;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   3.2
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		// Initialiase variables.
		$append  = parent::getRedirectToItemAppend($recordId, $urlVar);
		$context = $this->input->getCmd('jform[context]');
		$cid     = $this->input->getInt('jform[cid]');
		$return  = $this->input->get('return', null, 'base64');

		if ($context)
		{
			$append .= '&context=' . $context;
		}

		if ($cid)
		{
			$append .= '&cid=' . $cid;
		}

		if ($return)
		{
			$append .= '&return=' . $return;
		}

		return $append;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Initialiase variables.
		$result = parent::save($key, $urlVar);
		$return = $this->input->get('return', null, 'base64');

		if ($this->getTask() == 'save' && $return)
		{
			// Redirect to the return page.
			$this->setRedirect(base64_decode($return));
		}

		return $result;
	}
}
