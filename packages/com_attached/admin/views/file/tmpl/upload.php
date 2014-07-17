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

// Add JavaScript Frameworks.
JHtml::_('jquery.framework');

// Load JavaScript.
JHtml::script('com_attached/jquery.bootbox.min.js', false, true);

// Initialiase variables.
$this->hiddenFieldsets    = array();
$this->hiddenFieldsets[0] = 'basic-limited';
$this->configFieldsets    = array();
$this->configFieldsets[0] = 'editorConfig';

// Get the input.
$input = JFactory::getApplication()->input;

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$params = json_decode($params);

// Load Stylesheet.
JHtml::stylesheet('com_attached/backend.css', false, true, false);

$context = $input->getCmd('context');
$cid     = $input->getInt('cid');

// Get an instance of the generic files model.
$model = JModelLegacy::getInstance('Files', 'AttachedModel', array('ignore_request' => true));
$model->setState('list.select', 'a.id, a.title, a.file, a.ordering, a.access, a.created, a.downloads');
$model->setState('list.ordering', 'a.ordering');
$model->setState('filter.context', $context);
$model->setState('filter.cid', $cid);

$files = $model->getItems();

// Get the full current URI.
$uri = JUri::getInstance();
$return = base64_encode($uri);

$saveOrderingUrl = 'index.php?option=com_attached&task=files.saveOrderAjax&tmpl=component';
JHtml::_('sortablelist.sortable', 'fileList', 'item-form', 'asc', $saveOrderingUrl);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'file.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			// if (window.opener && (task == 'file.save' || task == 'file.cancel')) {
				// window.opener.document.closeEditWindow = self;
				// window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
			// }

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}

	jQuery(document).ready(function($) {
		// Delete the entry once we have confirmed that it should be deleted.
		$('a.delete').live('click', function(event) {
			event.preventDefault();

			var row = $(event.currentTarget).closest('tr');

			bootbox.confirm('<?php echo JText::_('COM_ATTACHED_CONFIRM_PROCEED_DELETE'); ?>', '<?php echo JText::_('JNO'); ?>', '<?php echo JText::_('JYES'); ?>', function(result) {
				if (result) {
					$.ajax({
						url: 'index.php?option=com_attached&task=file.deleteFileAjax&tmpl=component&format=json',
						type: 'POST',
						data: {
							cid: $(row).find('input[name="cid"]').val(),
							'<?php echo JSession::getFormToken(); ?>': 1
						},
						success: function() {
							$(row).remove();
						}
					});
				};
			});
		});
	});
</script>
<div class="container-popup">
	<form action="<?php echo JRoute::_('index.php?option=com_attached&layout=upload&tmpl=component&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
		<div class="form-horizontal">
			<div class="page-header">
				<h3><?php echo JText::_('COM_ATTACHED_ADD_ATTACHED'); ?></h3>
			</div>
			<div class="well">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="adminform">
							<?php echo $this->form->getControlGroup('file'); ?>
							<?php echo $this->form->getControlGroup('title'); ?>
							<?php echo $this->form->getControlGroup('credits'); ?>
							<div class="form-actions">
								<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('file.save');"><i class="icon-upload"></i> <?php echo JText::_('JTOOLBAR_UPLOAD'); ?></button>
								<!-- <button type="button" class="btn" onclick="Joomla.submitbutton('file.cancel');"><?php echo JText::_('JCANCEL'); ?></button> -->
							</div>
						</fieldset>
					</div>
				</div>
			</div>
			<table class="table table-striped table-hover" id="fileList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<i class="icon-menu-2"></i>
						</th>
						<th class="title">
							<?php echo JText::_('COM_ATTACHED_HEADING_TITLE'); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JText::_('COM_ATTACHED_HEADING_DATE'); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JText::_('COM_ATTACHED_HEADING_ACTIONS'); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JText::_('JGRID_HEADING_ID'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if ($files):
						foreach ($files as $i => $file):
							$filename = isset($file->file['name']) ? $file->file['name'] : '';
							$path     = '/uploads/' . str_replace('.', '/', $context) . '/' . $filename;
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="order nowrap center hidden-phone">
									<span class="sortable-handler hasTooltip" title="">
										<i class="icon-menu"></i>
									</span>
									<input type="checkbox" style="display:none" name="cid[]" value="<?php echo $file->id; ?>" />
									<input type="text" style="display:none" name="order[]" value="<?php echo $file->ordering; ?>" />
								</td>
								<td class="nowrap">
									<?php if (JFile::exists(JPATH_ROOT . $path)): ?>
										<a href="<?php echo JUri::root() . $path; ?>"><?php echo $this->escape($file->title); ?></a>
									<?php else: ?>
										<?php echo $this->escape($file->title); ?> <?php echo JText::_('COM_ATTACHED_FILE_NOT_EXISTS'); ?>
									<?php endif; ?>
								</td>
								<td class="nowrap">
									<?php echo JHtml::_('date', $file->created, JText::_('DATE_FORMAT_LC2')); ?>
								</td>
								<td class="nowrap">
									<!-- <a href="#" class="btn btn-default btn-small"><i class="icon-edit"></i> <?php echo JText::_('JTOOLBAR_EDIT'); ?></a> -->
									<a href="#" class="btn btn-danger btn-small delete"><i class="icon-remove"></i> <?php echo JText::_('JTOOLBAR_DELETE'); ?></a>
									<input type="hidden" name="cid" value="<?php echo $file->id; ?>">
								</td>
								<td class="center hidden-phone">
									<?php echo (int) $file->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5"><?php echo JText::_('COM_ATTACHED_NO_MATCHING_RESULTS'); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="jform[state]" value="1" />
				<input type="hidden" name="jform[language]" value="*" />
				<input type="hidden" name="jform[context]" value="<?php echo $context; ?>" />
				<input type="hidden" name="jform[cid]" value="<?php echo $cid; ?>" />
				<input type="hidden" name="return" value="<?php echo $input->getCmd('return', $return); ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
