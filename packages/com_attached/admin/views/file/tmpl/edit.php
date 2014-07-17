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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the behavior script.
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Initialiase variables.
$this->hiddenFieldsets    = array();
$this->hiddenFieldsets[0] = 'basic-limited';
$this->configFieldsets    = array();
$this->configFieldsets[0] = 'editorConfig';

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$params = json_decode($params);

if (!isset($params->show_publishing_options))
{
	$params->show_publishing_options = '1';
	$params->show_file_options = '1';
}

// Check if the file uses configuration settings besides global. If so, use them.
if (isset($this->item->params['show_publishing_options']) && $this->item->params['show_publishing_options'] != '')
{
	$params->show_publishing_options = $this->item->params['show_publishing_options'];
}

if (isset($this->item->params['show_file_options']) && $this->item->params['show_file_options'] != '')
{
	$params->show_file_options = $this->item->params['show_file_options'];
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'file.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_attached&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_ATTACHED_FIELDSET_FILE_CONTENT', true)); ?>
				<div class="row-fluid">
					<div class="span9">
						<fieldset class="adminform">
							<?php echo $this->form->getControlGroup('context'); ?>
							<?php echo $this->form->getControlGroup('cid'); ?>
							<?php echo $this->form->getControlGroup('credits'); ?>
							<div class="control-group">
								<div class="control-label">
									<label><?php echo JText::_('COM_ATTACHED_FIELD_FILE_LABEL'); ?></label>
								</div>
								<div class="controls">
									<a href="<?php echo JRoute::_(JUri::root() . 'index.php?option=com_attached&task=download&id=' . $this->item->id, false); ?>" class="btn btn-default"><?php echo JText::_('COM_ATTACHED_FIELD_DOWNLOAD_LABEL') ?></a>
									<span class="badge badge-info"><?php echo JText::sprintf('COM_ATTACHED_DOWNLOADS', $this->item->downloads); ?></span>
								</div>
							</div>
							<?php echo $this->form->getInput('description'); ?>
						</fieldset>
					</div>
					<div class="span3">
						<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php // Do not show the publishing options if the edit form is configured not to. ?>
			<?php if ($params->show_publishing_options == 1): ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_ATTACHED_FIELDSET_PUBLISHING', true)); ?>
					<div class="row-fluid form-horizontal-desktop">
						<div class="span6">
							<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
						</div>
						<div class="span6">
							<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
						</div>
					</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php if (JLanguageAssociations::isEnabled()): ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
					<?php echo JLayoutHelper::render('joomla.edit.associations', $this); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php $this->show_options = $params->show_file_options; ?>
			<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

			<?php if ($this->canDo->get('core.admin')): ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('COM_ATTACHED_FIELDSET_SLIDER_EDITOR_CONFIG', true)); ?>
					<?php foreach ($this->form->getFieldset('editorConfig') as $field): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php if ($this->canDo->get('core.admin')): ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_ATTACHED_FIELDSET_RULES', true)); ?>
					<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->getCmd('return'); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
