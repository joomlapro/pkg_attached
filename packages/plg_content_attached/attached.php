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
 * Joomla Attached plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.3
 */
class PlgContentAttached extends JPlugin
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An array that holds the plugin configuration.
	 *
	 * @access  protected
	 * @since   3.3
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string   $context     The context for the content passed to the plugin.
	 * @param   object   &$article    The content object. Note $article->text is also available
	 * @param   object   &$params     The content params
	 * @param   integer  $limitstart  The 'page' number
	 *
	 * @return  string
	 *
	 * @since   3.3
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $limitstart = 0)
	{
		// Initialiase variables.
		$app = JFactory::getApplication();

		// Check that we are in the site application and if have id in article.
		if ($app->isAdmin() || !isset($article->id))
		{
			return;
		}

		// Load the backend helper.
		require_once JPATH_ADMINISTRATOR . '/components/com_attached/helpers/files.php';

		// Include dependancies.
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_attached/models', 'AttachedModel');

		// Get an instance of the generic files model.
		$model = JModelLegacy::getInstance('Files', 'AttachedModel', array('ignore_request' => true));
		$model->setState('list.select', 'a.id, a.title, a.file, a.credits, a.access, a.created, a.downloads');
		$model->setState('filter.context', $context);
		$model->setState('filter.cid', $article->id);
		$model->setState('filter.state', 1);
		$model->setState('list.ordering', 'a.ordering');

		$items = $model->getItems();

		if ($items)
		{
			$total = count($items);
			$html  = array();

			$html[] = '<div class="attached-files">';
			$html[] = '<h3>' . JText::_('PLG_CONTENT_ATTACHED_TITLE') . ' <small class="pull-right attached-total">' . JText::sprintf('PLG_CONTENT_ATTACHED_TOTAL', $total) . '</small></h3>';
			$html[] = '<table class="table table-bordered table-hover">';
			$html[] = '<thead>';
			$html[] = '<tr>';
			$html[] = '<th>' . JText::_('PLG_CONTENT_ATTACHED_HEADING_TITLE') . '</th>';
			$html[] = '<th class="nowrap" width="10%">' . JText::_('PLG_CONTENT_ATTACHED_HEADING_TYPE') . '</th>';
			$html[] = '<th class="nowrap" width="10%">' . JText::_('PLG_CONTENT_ATTACHED_HEADING_SIZE') . '</th>';
			$html[] = '<th class="nowrap" width="10%">' . JText::_('PLG_CONTENT_ATTACHED_HEADING_DATE') . '</th>';
			$html[] = '<th class="nowrap" width="10%">' . JText::_('PLG_CONTENT_ATTACHED_HEADING_DOWNLOADS') . '</th>';
			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			jimport('joomla.filesystem.file');

			foreach ($items as $item)
			{
				$path = '/uploads/' . str_replace('.', '/', $context) . '/' . $item->file['name'];

				$html[] = '<tr>';
				$html[] = '<td>';

				if (JFile::exists(JPATH_ROOT . $path))
				{
					$html[] = '<a href="' . JRoute::_('index.php?option=com_attached&task=download&id=' . $item->id, false) . '">' . $item->title . '</a>';
				}
				else
				{
					$html[] = $item->title . ' ' . JText::_('PLG_CONTENT_ATTACHED_FILE_NOT_EXISTS');
				}

				if ($item->credits)
				{
					$html[] = '<small class="attached-credits">' . JText::sprintf('PLG_CONTENT_ATTACHED_CREDITS', $item->credits) . '</small>';
				}

				if (defined('JDEBUG') && JDEBUG)
				{
					$html[] = '<br />';
					$html[] = '<span class="label label-warning">' . JText::_('PLG_CONTENT_ATTACHED_DEBUG') . '</span>';
					$html[] = '<small class="attached-filename">' . JText::sprintf('PLG_CONTENT_ATTACHED_FILENAME', $item->file['name']) . '</small>';
				}
				$html[] = '</td>';
				$html[] = '<td class="nowrap right">' . strtoupper($item->file['extension']) . '</td>';
				$html[] = '<td class="nowrap right">' . JHtml::_('number.bytes', $item->file['size']) . '</td>';
				$html[] = '<td class="nowrap right">' . JHtml::_('date', $item->created, JText::_('PLG_CONTENT_ATTACHED_DATE_FORMAT')) . '</td>';
				$html[] = '<td class="right">' . $item->downloads . '</td>';
				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table>';
			$html[] = '</div>';

			return implode("\n", $html);
		}

		return;
	}
}
