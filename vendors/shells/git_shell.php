<?php
/**
 * Short description
 *
 * Long description
 *
 *
 * Copyright 2008, Garrett J. Woodworth <gwoo@cakephp.org>
 * Licensed under The MIT License
 * Redistributions of files must retain the copyright notice.
 *
 * @copyright		Copyright 2008, Garrett J. Woodworth
 * @package			chaw
 * @subpackage		chaw.vendors.sheels
 * @since			Chaw 0.1
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */
class GitShellShell extends Shell {

	var $uses = array('Project', 'Permission', 'Git');

	var $actionMap = array(
		'git-upload-pack' => 'r',
		'git-receive-pack' => 'rw',
	);
/**
 * undocumented function
 *
 * @return void
 *
 **/
	function main() {
		if (empty($this->params['user'])) {
			$this->err('User not found.');
			return 1;
		}
		
		$command = $this->args[0];
		
		if (!isset($this->actionMap[$command])) {
			$this->err('Command not found.');
			return 1;
		}

		$project = rtrim(trim($this->args[1], "'"), '.git');

		$this->Project->initialize(compact('project'));

		$allowed = $this->Permission->check('/refs/heads/master', array(
			'user' => $this->params['user'],
			'group' => @$permissions['Permission']['group'],
			'access' => $this->actionMap[$command],
			'default' => false
		));

		if ($allowed === true) {
			$path = Configure::read('Content.git');
			$this->Git->config(array(
				'repo' => $path .'repo' . DS . $project . '.git',
				'working' => $path .'working' . DS . $project
			));

			passthru("{$command} {$path}repo/{$project}", $result);
			return $result;

		}

		$this->err('Authorization failed');
		return 1;
	}
/**
 * undocumented function
 *
 * @return void
 *
 **/
	function _welcome() {}
}