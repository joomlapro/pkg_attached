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

// Load the helper class.
JLoader::register('AttachedHelper', JPATH_ADMINISTRATOR . '/components/com_attached/helpers/attached.php');

/**
 * Item Model for an File.
 *
 * @package     Attached
 * @subpackage  com_attached
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class AttachedModelFile extends JModelAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $text_prefix = 'COM_ATTACHED_FILE';

	/**
	 * The type alias for this content type (for example, 'com_attached.file').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_attached.file';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   3.2
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			// Get the current user object.
			$user = JFactory::getUser();

			return $user->authorise('core.delete', 'com_attached.file.' . (int) $record->id);
		}
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   3.2
	 */
	protected function canEditState($record)
	{
		// Get the current user object.
		$user = JFactory::getUser();

		// Check for existing file.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_attached.file.' . (int) $record->id);
		}
		// Default to component settings if neither file.
		else
		{
			return parent::canEditState('com_attached');
		}
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function prepareTable($table)
	{
		// Set the publish date to now.
		$db = $this->getDbo();

		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}

		// Increment the files version number.
		$table->version++;

		// Reorder the files so the new file is first.
		if (empty($table->id))
		{
			$table->reorder('context = "' . $table->context . '" AND cid = ' . $table->cid . ' AND state >= 0');
		}
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate.
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object.
	 *
	 * @since   3.2
	 */
	public function getTable($type = 'File', $prefix = 'AttachedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the file field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->file);
			$item->file = $registry->toArray();

			if (!empty($item->id))
			{
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_attached.file');
			}
		}

		// Load associated files items.
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$item->associations = array();

			if ($item->id != null)
			{
				$associations = JLanguageAssociations::getAssociations('com_attached', '#__attached_files', 'com_attached.item', $item->id);

				foreach ($associations as $tag => $association)
				{
					$item->associations[$tag] = $association->id;
				}
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_attached.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}

		// Determine correct permissions to check.
		if ($this->getState('file.id'))
		{
			$id = $this->getState('file.id');
		}

		// Get the current user object.
		$user = JFactory::getUser();

		// Check for existing file.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_attached.file.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_attached')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an file you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		// Prevent messing with file language.
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($app->isSite() && $assoc && $this->getState('file.id'))
		{
			$form->setFieldAttribute('language', 'readonly', 'true');
			$form->setFieldAttribute('language', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   3.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = JFactory::getApplication();
		$data = $app->getUserState('com_attached.edit.file.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_attached.file', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function save($data)
	{
		// Get the application.
		$app = JFactory::getApplication();

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// TODO: Need better this code!
		// Get some data from the request.
		$file = $app->input->files->get('jform', '', 'array');

		if (isset($file['file']['size']) && $file['file']['size'] > 1)
		{
			$filepath      = JPath::clean(JPATH_ROOT . '/uploads/' . str_replace('.', '/', $data['context']) . '/');
			$extension     = JFile::getExt($file['file']['name']);

			if (!JFolder::exists($filepath))
			{
				JFolder::create($filepath);
			}

			if (!trim($data['title']))
			{
				$data['title'] = JFile::stripExt($file['file']['name']);
			}

			$data['file']['name']      = uniqid() . '.' . $extension;
			$data['file']['type']      = $file['file']['type'];
			$data['file']['size']      = $file['file']['size'];
			$data['file']['extension'] = $extension;
			$file['file']['name']      = $data['file']['name'];

			$object_file = new JObject($file['file']);
			$object_file->filepath = $filepath . strtolower($file['file']['name']);

			JFile::upload($object_file->tmp_name, $object_file->filepath);
		}
		else
		{
			JError::raiseNotice(100, JText::_('COM_ATTACHED_EMPTY_FILE'));
		}

		if (parent::save($data))
		{
			$assoc = JLanguageAssociations::isEnabled();

			if ($assoc)
			{
				$id   = (int) $this->getState($this->getName() . '.id');
				$item = $this->getItem($id);

				// Adding self to the association.
				$associations = $data['associations'];

				foreach ($associations as $tag => $id)
				{
					if (empty($id))
					{
						unset($associations[$tag]);
					}
				}

				// Detecting all item menus.
				$all_language = $item->language == '*';

				if ($all_language && !empty($associations))
				{
					JError::raiseNotice(403, JText::_('COM_ATTACHED_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}

				$associations[$item->language] = $item->id;

				// Deleting old association for these items.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where('context=' . $db->quote('com_attached.item'))
					->where('id IN (' . implode(',', $associations) . ')');
				$db->setQuery($query);
				$db->execute();

				if ($error = $db->getErrorMsg())
				{
					$this->setError($error);

					return false;
				}

				if (!$all_language && count($associations))
				{
					// Adding new association for these items.
					$key = md5(json_encode($associations));
					$query->clear()
						->insert('#__associations');

					foreach ($associations as $id)
					{
						$query->values($id . ',' . $db->quote('com_attached.item') . ',' . $db->quote($key));
					}

					$db->setQuery($query);
					$db->execute();

					if ($error = $db->getErrorMsg())
					{
						$this->setError($error);

						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 *
	 * @since   3.2
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'context = "' . (string) $table->context . '"';
		$condition[] = 'cid = ' . (int) $table->cid;

		return $condition;
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import.
	 *
	 * @return  void
	 *
	 * @since   $TM_VERSION
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Association files items.
		$app   = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');

			// Force to array (perhaps move to $this->loadFormData()).
			$data = (array) $data;

			$addform  = new SimpleXMLElement('<form />');
			$fields   = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_ATTACHED_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;

			foreach ($languages as $tag => $language)
			{
				if (empty($data['language']) || $tag != $data['language'])
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_article');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
				}
			}

			if ($add)
			{
				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to increment the download counter for the file.
	 *
	 * @param   integer  $pk  Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since   3.2
	 */
	public function download($pk = null)
	{
		if (empty($pk))
		{
			$pk = $this->getState('file.id');
		}

		$file = $this->getTable('File', 'AttachedTable');

		return $file->download($pk);
	}
}
