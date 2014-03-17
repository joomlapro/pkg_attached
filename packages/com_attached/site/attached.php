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

// Execute the task.
$controller = JControllerLegacy::getInstance('Attached');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
