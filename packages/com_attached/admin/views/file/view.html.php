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
 * View to edit a file.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class AttachedViewFile extends JViewLegacy
{
	/**
	 * The form to use for the view.
	 *
	 * @var     JForm
	 */
	protected $form;

	/**
	 * The item to edit.
	 *
	 * @var     JObject
	 */
	protected $item;

	/**
	 * The model state.
	 *
	 * @var     JObject
	 */
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		try
		{
			// Initialiase variables.
			$this->form  = $this->get('Form');
			$this->item  = $this->get('Item');
			$this->state = $this->get('State');
			$this->canDo = FilesHelper::getActions(0, 'com_attached');
		}
		catch (Exception $e)
		{
			JErrorPage::render($e);

			return false;
		}

		if ($this->getLayout() == 'modal')
		{
			$this->form->setFieldAttribute('language', 'readonly', 'true');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		// Initialiase variables.
		$user       = JFactory::getUser();
		$userId     = $user->get('id');
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Since we do not track these assets at the item level.
		$canDo      = $this->canDo;

		JToolbarHelper::title(JText::_('COM_ATTACHED_PAGE_' . ($checkedOut ? 'VIEW_FILE' : ($isNew ? 'ADD_FILE' : 'EDIT_FILE'))), 'pencil-2 file-add');

		// Built the actions for new and existing records.
		// For new records, check the create permission.
		if ($isNew)
		{
			JToolbarHelper::apply('file.apply');
			JToolbarHelper::save('file.save');
			// JToolbarHelper::save2new('file.save2new');
			JToolbarHelper::cancel('file.cancel');
		}
		else
		{
			// Can not save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolbarHelper::apply('file.apply');
					JToolbarHelper::save('file.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					// if ($canDo->get('core.create'))
					// {
					// 	JToolbarHelper::save2new('file.save2new');
					// }
				}
			}

			if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit'))
			{
				JToolbarHelper::versions('com_attached.file', $this->item->id);
			}

			JToolbarHelper::cancel('file.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::help('file', $com = true);
	}
}
