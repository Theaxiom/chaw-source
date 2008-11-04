<div id="current-user">
	<?php if (!empty($CurrentUser)):?>
		<span class="gravatar">
			<?php
				$gravatar = "http://www.gravatar.com/avatar/" . md5($CurrentUser->email). "?"
				 	. "size=22";
				echo "<img src=\"{$gravatar}\" />";
			?>
		</span>
		<span class="username">
			<?php echo $html->link($CurrentUser->username, array('admin' => false, 'project' => false, 'controller' => 'users', 'action' => 'account')); ?>
		</span>
	<?php else:?>
		<span class="login">
			<?php echo $html->link('Login', array('project' => false, 'controller' => 'users', 'action' => 'login')); ?>
			or
			<?php echo $html->link('Register', array('project' => false, 'controller' => 'users', 'action' => 'add')); ?>
		</span>
	<?php endif;?>
</div>
