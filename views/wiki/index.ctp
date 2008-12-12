<?php
$html->css('highlight/idea', null, null, false);
$javascript->link('highlight', false);

$script = '
hljs.initHighlightingOnLoad();

$(document).ready(function(){
	$(".wiki-text").each(function () {
		$(this).html(converter.makeHtml(jQuery.trim($(this).text())))
	});
});
';
$javascript->codeBlock($script, array('inline' => false));
?>
<div class="page-navigation">
	<?php echo $html->link('Edit', array('controller' => 'wiki', 'action' => 'edit', $path, $slug));?>
	|
	<?php echo $html->link('New', array('controller' => 'wiki', 'action' => 'add', $path, $slug, 1));?>
</div>

<div class="breadcrumbs">
	<?php echo $chaw->breadcrumbs($path, $slug);?>
</div>


<?php if (!empty($page) || !empty($paths)):?>
<div class="wiki-navigation">
	<?php if(!empty($wiki) && !empty($page)):
		$data = h($page['Wiki']['content']);
	?>
		<div class="description">
			<?php if (strpos($data, '##') === false):?>
				<h2><?php echo Inflector::humanize($slug);?></h2>
			<?php endif;?>

			<div class="wiki-text">
				<?php echo $data;?>
			</div>
		</div>
	<?php endif;?>

	<?php if (!empty($paths)):?>
		<?php
			$nav = null;
			foreach ($paths as $category):
				if (str_replace($slug, '', $category) !== '/' && $category != $path . '/' . $slug) :
					$nav .= $html->tag('li',
						$html->link($category, array($category))
					);
				endif;
			endforeach;
			if (!empty($nav)) {
				echo $html->tag('div',
					'<h3>Wiki Nav</h3>' .
					$html->tag('ul', $nav), array('class' => 'paths')
				);
			}
		?>
	<?php endif;?>

	<?php if (!empty($recents)):?>
		<?php
			$nav = null;
			foreach ($recents as $recent):
					$nav .= $html->tag('li',
						$html->link(rtrim($recent['Wiki']['path']. '/', '/') . '/' . $recent['Wiki']['slug'], array($recent['Wiki']['path'], $recent['Wiki']['slug']))
					);
			endforeach;
			if (!empty($nav)) {
				echo $html->tag('div',
					'<h3>Recent Entries</h3>' .
					$html->tag('ul', $nav), array('class' => 'paths')
				);
			}
		?>
	<?php endif;?>

	<?php
		if(!empty($this->params['isAdmin'])) {
			$links = array();
			if(empty($page['Wiki']['active'])) {
				$links[] = $html->link('activate', array('action' => 'activate', $page['Wiki']['id']));
			}
			$links[] = $html->link('delete', array('action' => 'delete', $page['Wiki']['id']));

			//echo $html->tag('div', join(' | ', $links), array('class' => 'admin'));
		}

	?>

	<?php if (!empty($revisions)):?>
		<div class="revisions">
		<?php
			echo $form->create(array('action' => 'index', array('url' => array($path, $slug))));
			echo $form->input('revision', array('value' => $page['Wiki']['id']));
			echo $html->tag('div',
				$form->submit('view', array('div' => false, 'name' => 'view'))
				. $form->submit('activate', array('div' => false, 'name' => 'activate'))
				. $form->submit('delete', array('div' => false, 'name' => 'delete')),
				array('class' => 'submit')
			);
			echo $form->end();
		?>
		</div>
	<?php endif;?>

</div>
<?php endif; ?>

<div class="wiki-content">
	<?php if(!empty($wiki)):?>

		<?php foreach($wiki as $content):
			$data = h($text->truncate($content['Wiki']['content'], 420, '...', false, true));
		?>
			<?php if (strpos($data, '##') === false):?>
				<h3><?php echo $html->link(Inflector::humanize($content['Wiki']['slug']), array('controller' => 'wiki', 'action' => 'index', $content['Wiki']['path'], $content['Wiki']['slug']));?></h3>
			<?php endif; ?>

			<div class="wiki-text">
				<?php echo $data; ?>
			</div>

			<div class="actions">
				<?php echo $html->link('View', array('controller' => 'wiki', 'action' => 'index', $content['Wiki']['path'], $content['Wiki']['slug']));?>
				|
				<?php echo $html->link('Edit', array('controller' => 'wiki', 'action' => 'edit', $content['Wiki']['path'], $content['Wiki']['slug']));?>
				|
				<?php echo $html->link('New', array('controller' => 'wiki', 'action' => 'add', $content['Wiki']['path'], $content['Wiki']['slug'], 1));?>
			</div>

		<?php endforeach; ?>

	<?php elseif(!empty($page)):
		$data = h($page['Wiki']['content']);
	?>
		<?php if (strpos($data, '##') === false):?>
			<h2><?php echo Inflector::humanize($slug);?></h2>
		<?php endif;?>

		<div class="wiki-text">
			<?php echo $data;?>
		</div>

	<?php endif; ?>
</div>
