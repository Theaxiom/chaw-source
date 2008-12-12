<?php
$html->css('highlight/idea', null, null, false);
$javascript->link('highlight', false);

$script = '
hljs.initHighlightingOnLoad();

$(document).ready(function(){
	var text = jQuery.trim($("#WikiContent").val());
	$("#Preview").html(converter.makeHtml(text));
	$("#WikiContent").bind("keyup", function() {
		$("#Preview").html(converter.makeHtml($(this).val()));
		hljs.initHighlighting.called = false;
		hljs.initHighlighting();
	});
});
';
$javascript->codeBlock($script, array('inline' => false));
?>
<div class="wiki form">

	<div class="breadcrumbs">
		<?php echo $chaw->breadcrumbs($path);?>
	</div>
	
	<div id="Preview" class="wiki-content wiki-text"></div>
	
	<?php echo $form->create(array('url' => '/' . $this->params['url']['url']));?>
		
		<fieldset>			
		<?php
			echo $form->hidden('update');
			
			echo $form->input('path', array('div' => 'input text path',
				'label' => "use a path to group pages into categories and subcategories. example: /{$CurrentUser->username}/posts",
			));

			if ($form->value('slug')) {
				echo $form->hidden('slug');
				echo $form->input('slug', array('label' => false, 'disabled' => true));
			} else {
				echo $form->input('title', array('label' => 'Title'));
			}

			echo $form->input('content', array('label' => false));

			/*if ($form->value('path')) {
				echo $form->input('path', array('div' => 'input text path',
					'between' => '<em>group pages</em>',
					'after' => "use paths to group pages into categories and subcategories. example: blog/{$CurrentUser->username}",
				));
			}*/
		?>
		</fieldset>


	<?php echo $form->end('Submit');?>

	<div class="help">
		<?php echo $this->element('markdown_help'); ?>
	</div>

</div>