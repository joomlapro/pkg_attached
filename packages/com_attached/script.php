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
 * Script file of Attached Component.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.3
 */
class Com_AttachedInstallerScript
{
	/**
	 * Called before any type of action.
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install).
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function preflight($route, JAdapterInstance $adapter)
	{

	}

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
		// Adding content type for files.
		$table = JTable::getInstance('Contenttype', 'JTable');

		if (!$table->load(array('type_alias' => 'com_attached.file')))
		{
			// Table column.
			$special = new stdClass;
			$special->dbtable = '#__attached_files';
			$special->key     = 'id';
			$special->type    = 'File';
			$special->prefix  = 'AttachedTable';
			$special->config  = 'array()';

			$common = new stdClass;
			$common->dbtable  = '#__ucm_content';
			$common->key      = 'ucm_id';
			$common->type     = 'Corecontent';
			$common->prefix   = 'JTable';
			$common->config   = 'array()';

			$table_object = new stdClass;
			$table_object->special = $special;
			$table_object->common  = $common;

			// Field mappings column.
			$common = new stdClass;
			$common->core_content_item_id = 'id';
			$common->core_title           = 'title';
			$common->core_state           = 'state';
			$common->core_alias           = 'null';
			$common->core_created_time    = 'created';
			$common->core_modified_time   = 'modified';
			$common->core_body            = 'description';
			$common->core_hits            = 'hits';
			$common->core_publish_up      = 'publish_up';
			$common->core_publish_down    = 'publish_down';
			$common->core_access          = 'access';
			$common->core_params          = 'params';
			$common->core_featured        = 'null';
			$common->core_metadata        = 'metadata';
			$common->core_language        = 'language';
			$common->core_images          = 'null';
			$common->core_urls            = 'null';
			$common->core_version         = 'version';
			$common->core_ordering        = 'ordering';
			$common->core_metakey         = 'metakey';
			$common->core_metadesc        = 'metadesc';
			$common->core_catid           = 'null';
			$common->core_xreference      = 'xreference';
			$common->asset_id             = 'asset_id';

			$field_mappings = new stdClass;
			$field_mappings->common  = $common;
			$field_mappings->special = new stdClass;

			// Content history options column.
			$hideFields = array(
				'asset_id',
				'checked_out',
				'checked_out_time',
				'version'
			);

			$ignoreChanges = array(
				'modified_by',
				'modified',
				'checked_out',
				'checked_out_time',
				'version',
				'hits'
			);

			$convertToInt = array(
				'publish_up',
				'publish_down',
				'ordering'
			);

			$displayLookup = array(
				array(
					'sourceColumn' => 'created_by',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'access',
					'targetTable' => '#__viewlevels',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'modified_by',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				)
			);

			$content_history_options = new stdClass;
			$content_history_options->formFile      = 'administrator/components/com_attached/models/forms/file.xml';
			$content_history_options->hideFields    = $hideFields;
			$content_history_options->ignoreChanges = $ignoreChanges;
			$content_history_options->convertToInt  = $convertToInt;
			$content_history_options->displayLookup = $displayLookup;

			$content_types['type_title']              = 'File';
			$content_types['type_alias']              = 'com_attached.file';
			$content_types['table']                   = json_encode($table_object);
			$content_types['rules']                   = '';
			$content_types['field_mappings']          = json_encode($field_mappings);
			$content_types['router']                  = 'AttachedHelperRoute::getFileRoute';
			$content_types['content_history_options'] = json_encode($content_history_options);

			$table->save($content_types);
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
		$adapter->getParent()->setRedirectURL('index.php?option=com_attached');
	}

	/**
	 * Called on update.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function update(JAdapterInstance $adapter)
	{

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
		echo '<p>' . JText::_('COM_ATTACHED_UNINSTALL_TEXT') . '</p>';
	}
}
