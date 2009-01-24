<?php
/**
 * Short description
 *
 * Long description
 *
 * Copyright 2008, Garrett J. Woodworth <gwoo@cakephp.org>
 * Redistributions not permitted
 *
 * @copyright		Copyright 2008, Garrett J. Woodworth
 * @package			chaw
 * @subpackage		chaw.controllers
 * @since			Chaw 0.1
 * @license			commercial
 *
 */
class CommitsController extends AppController {

	var $name = 'Commits';

	var $helpers = array('Time', 'Text');

	var $paginate = array('order' => 'Commit.created DESC');

	function index() {
		$this->Commit->recursive = 0;
		$this->Commit->bindModel(array('hasOne' => array(
			'Timeline' => array(
				'className' => 'Timeline',
				'foreignKey' => 'foreign_key',
				'conditions' => array('Timeline.model = \'Commit\''),
		))), false);

		$conditions = array('Commit.project_id' => $this->Project->id);
		$this->set('commits', $this->paginate('Commit', $conditions));
	}

	function view($revision = null) {
		$commit = $this->Commit->findByRevision($revision);
		$this->set('commit', $commit);
	}

	function history() {
		$args = func_get_args();
		$path = join(DS, $args);

		$current = null;

		if ($args > 0) {
			$current = array_pop($args);
		}

		$commits = $this->paginate($this->Project->Repo, array('path' => $path));

		$this->set(compact('commits', 'args', 'current'));
	}

	function remove($id = null) {
		if (!$id || empty($this->params['isAdmin'])) {
			$this->redirect($this->referer());
		}

		$this->Commit->bindModel(array('hasOne' => array(
			'Timeline' => array(
				'className' => 'Timeline',
				'foreignKey' => 'foreign_key',
				'conditions' => array('Timeline.model = \'Commit\''),
				'dependent' => true
		))), false);

		if ($this->Commit->del($id)) {
			$this->Session->setFlash('The commit was deleted');
		} else {
			$this->Session->setFlash('The commit was NOT deleted');
			if ($timeline = $this->Commit->Timeline->find('id', array('Timeline.foreign_key' => $id, 'Timeline.model = \'Commit\''))) {
				if ($this->Commit->Timeline->del($timeline)) {
					$this->Session->setFlash('The commit was removed from timeline');
				}
			}
		}
		$this->redirect(array('action' => 'index'));
	}
}
?>