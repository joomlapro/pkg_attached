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
 * View class for a list of files.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class AttachedViewFiles extends JViewLegacy
{
	/**
	 * List of update items.
	 *
	 * @var     array
	 */
	protected $items;

	/**
	 * List pagination.
	 *
	 * @var     JPagination
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var     JObject
	 */
	protected $state;

	/**
	 * List of authors.
	 *
	 * @var     array
	 */
	protected $authors;

	/**
	 * The form filter.
	 *
	 * @var     JForm
	 */
	public $filterForm;

	/**
	 * List of active filters.
	 *
	 * @var     array
	 */
	public $activeFilters;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  Exception on failure, void on success.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		try
		{
			// Initialise variables.
			$this->items         = $this->get('Items');
			$this->pagination    = $this->get('Pagination');
			$this->state         = $this->get('State');
			$this->authors       = $this->get('Authors');
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}
		catch (Exception $e)
		{
			JErrorPage::render($e);

			return false;
		}

		// We do not need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

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
		// Initialise variables.
		$state = $this->get('State');
		$canDo = FilesHelper::getActions(0, 'com_attached');
		$user  = JFactory::getUser();

		// Get the toolbar object instance.
		$bar   = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_ATTACHED_MANAGER_FILES_TITLE'), 'stack files');

		// if ($canDo->get('core.create'))
		// {
		// 	JToolbarHelper::addNew('file.add');
		// }

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('file.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('files.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('files.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('files.archive');
			JToolbarHelper::checkin('files.checkin');
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'files.delete');
		}

		// Add a batch button.
		if ($user->authorise('core.create', 'com_attached') && $user->authorise('core.edit', 'com_attached') && $user->authorise('core.edit.state', 'com_attached'))
		{
			// Load the modal bootstrap script.
			JHtml::_('bootstrap.modal', 'collapseModal');

			// Instantiate a new JLayoutFile instance and render the batch button.
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = $layout->render(array('title' => $title));

			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($user->authorise('core.admin', 'com_attached'))
		{
			JToolbarHelper::preferences('com_attached');
		}

		JToolBarHelper::help('files', $com = true);
	}
}
