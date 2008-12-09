<?php
$html->css('highlight/idea', null, null, false);
$javascript->link('highlight', false);

$script = '
hljs.initHighlightingOnLoad();

$(document).ready(function(){
	converter = new Showdown.converter("' . $this->webroot . '");
	$(".wiki-text").each(function () {
		$(this).html(converter.makeHtml(jQuery.trim($(this).text())))
	});
});
';
$javascript->codeBlock($script, array('inline' => false));
?>
<div class="page-navigation">
	<?php echo $html->link('Edit', array('controller' => 'wiki', 'action' => 'add', $path, $slug));?>
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
			if ($nav) {
				echo $html->tag('div',
					'<h3>Wiki Nav</h3>',
					$html->tag('ul', $nav), array('class' => 'paths')
				);
			}
		?>
	<?php endif;?>

</div>
<?php endif; ?>

<div class="wiki-content">
	<?php if(!empty($wiki)):?>

		<?php foreach($wiki as $content):
			$data = h($text->truncate($content['Wiki']['content'], 100, '...', false, true));
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
				<?php echo $html->link('Edit', array('controller' => 'wiki', 'action' => 'add', $content['Wiki']['path'], $content['Wiki']['slug']));?>
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
