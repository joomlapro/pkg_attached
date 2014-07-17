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
 * File Table class.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.3
 */
class AttachedTableFile extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object.
	 *
	 * @since   3.3
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__attached_files', 'id', $db);

		// Register Observer for Tags.
		JObserverMapper::addObserverClassToClass('JTableObserverTags', 'AttachedTableFile', array('typeAlias' => 'com_attached.file'));

		// Register Observer for Version History.
		JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'AttachedTableFile', array('typeAlias' => 'com_attached.file'));
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   3.3
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_attached.file.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   3.3
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the parent asset id for the record.
	 *
	 * @param   JTable   $table  A JTable object (optional) for the asset parent.
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 *
	 * @since   3.3
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;

		// Build the query to get the asset id for the parent file.
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__assets'))
			->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_attached'));

		// Get the asset id from the database.
		$this->_db->setQuery($query);

		if ($result = $this->_db->loadResult())
		{
			$assetId = (int) $result;
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array.
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding. [optional]
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string.
	 *
	 * @see     JTable::bind()
	 * @since   3.3
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (isset($array['file']) && is_array($array['file']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['file']);
			$array['file'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @see     JTable::check()
	 * @since   3.3
	 */
	public function check()
	{
		// Check for valid title.
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_ATTACHED_ERR_TABLES_TITLE'));

			return false;
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			// Swap the dates.
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string.
		if (!empty($this->metakey))
		{
			// Only process if not empty.
			// Array of characters to remove.
			$bad_characters = array("\n", "\r", "\"", "<", ">");

			// Remove bad characters.
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey);

			// Create array using commas as delimiter.
			$keys = explode(',', $after_clean);
			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords.
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", ".
			$this->metakey = implode(", ", $clean_keys);
		}

		return true;
	}

	/**
	 * Overload the store method for the Files table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   3.3
	 */
	public function store($updateNulls = false)
	{
		// Initialiase variables.
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id)
		{
			// Existing item.
			$this->modified    = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			// New file. A file created and created_by field can be set by the user,
			// so we do not touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update. If not
	 *                             set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialiase variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Get the JDatabaseQuery object.
		$query = $this->_db->getQuery(true);

		// Update the publishing state for rows with the given primary keys.
		$query->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('state') . ' = ' . (int) $state)
			->where('(' . $where . ')' . $checkin);
		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}

	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @see     http://docs.joomla.org/JTableNested/delete
	 */
	public function delete($pk = null, $children = false)
	{
		jimport('joomla.filesystem.file');

		// Get an instance of the table table.
		$table = JTable::getInstance('File', 'AttachedTable');
		$table->load($pk);

		// Convert the file field to an array.
		$registry = new JRegistry;
		$registry->loadString($table->file);
		$table->file = $registry->toArray();

		$filename = isset($table->file['name']) ? $table->file['name'] : '';
		$filepath = JPath::clean(JPATH_ROOT . '/uploads/' . str_replace('.', '/', $table->context) . '/' . $filename);

		if (JFile::exists($filepath))
		{
			JFile::delete($filepath);
		}

		return parent::delete($pk, $children);
	}

	/**
	 * Method to increment the downloads for a row.
	 *
	 * @param   mixed  $pk  An optional primary key value to increment.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.3
	 */
	public function download($pk = null)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__attached_files'))
			->where($db->quoteName('id') . ' = ' . $db->quote($pk))
			->set($db->quoteName('downloads') . ' = (' . $db->quoteName('downloads') . ' + 1)');

		// Set the query and execute the update.
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		// Set table values in the object.
		$this->downloads++;

		return true;
	}
}
