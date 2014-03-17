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
	 * The default view.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $default_view = 'files';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   3.2
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Set the default view name and format from the Request.
		$view   = $this->input->get('view', $this->default_view);
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'file' && $layout == 'edit' && !$this->checkEditId('com_attached.edit.file', $id))
		{
			// Somehow the person just went to the form - we do not allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_attached&view=files', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
