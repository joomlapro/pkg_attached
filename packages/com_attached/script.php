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
 * Script file of Attached Component.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class Com_AttachedInstallerScript
{
	/**
	 * Called after any type of action.
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install).
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
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
			$common->core_alias           = null;
			$common->core_created_time    = 'created';
			$common->core_modified_time   = 'modified';
			$common->core_body            = 'description';
			$common->core_hits            = 'hits';
			$common->core_publish_up      = 'publish_up';
			$common->core_publish_down    = 'publish_down';
			$common->core_access          = 'access';
			$common->core_params          = 'params';
			$common->core_featured        = null;
			$common->core_metadata        = 'metadata';
			$common->core_language        = 'language';
			$common->core_images          = null;
			$common->core_urls            = null;
			$common->core_version         = 'version';
			$common->core_ordering        = 'ordering';
			$common->core_metakey         = 'metakey';
			$common->core_metadesc        = 'metadesc';
			$common->core_catid           = null;
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

		// Adding default params if installing or discovering.
		if ($route != 'update')
		{
			// Get an instance of the table table.
			$table = JTable::getInstance('Extension', 'JTable');
			$table->load(array('name' => 'com_attached'));
			$table->params = '{"show_title":"1","link_titles":"1","show_intro":"1","info_block_position":"0","show_author":"1","link_author":"0","show_create_date":"1","show_modify_date":"1","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_tags":"1","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_file_options":"1","save_history":"1","history_limit":10,"float_intro":"right","float_fulltext":"right","num_leading_files":"1","num_intro_files":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0","feed_show_readmore":"0"}';
			$table->store();
		}
	}

	/**
	 * Called on installation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function install(JAdapterInstance $adapter)
	{
		// Set the redirect location.
		$adapter->getParent()->setRedirectURL('index.php?option=com_attached');
	}

	/**
	 * Called on uninstallation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		echo '<p>' . JText::_('COM_ATTACHED_UNINSTALL_TEXT') . '</p>';
	}
}
