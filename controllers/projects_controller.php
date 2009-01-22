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
class ProjectsController extends AppController {

	var $name = 'Projects';

	var $paginate = array(
		'order' => 'Project.users_count DESC, Project.created ASC'
	);

	function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->mapActions(array('fork' => 'create'));
		$this->Auth->allow('index');
		$this->Access->allow('index');
	}

	function index() {
		if ($this->RequestHandler->ext == 'tar') {
			$this->set(array(
				'project' => basename($this->Project->Repo->working),
				'working' => $this->Project->Repo->working
			));

			$this->render('package');
		}

		Router::connectNamed(array('type', 'page'));

		$this->Project->recursive = 0;

		$this->paginate['conditions'] = array(
			'Project.private' => 0, 'Project.active' => 1, 'Project.approved' => 1
		);

		if ($this->params['isAdmin'] === true) {
			$this->paginate['conditions'] = array();
			$this->paginate['order'] = 'Project.id ASC';
		}

		$this->paginate['conditions']['Project.fork'] = null;

		if(!empty($this->passedArgs['type'])) {
			if ($this->passedArgs['type'] == 'fork') {
				$this->paginate['conditions']['Project.fork !='] = null;
			}
			unset($this->paginate['conditions']['Project.fork']);
		}

		$this->set('projects', $this->paginate());

		$this->set('rssFeed', array('controller' => 'projects'));
	}

	function forks() {
		$this->paginate['conditions'] = array(
			'Project.fork !=' => null, 'Project.project_id' =>  $this->Project->id
		);

		$this->set('projects', $this->paginate());
		$this->set('rssFeed', array('controller' => 'projects', 'action' => 'forks'));

		$this->render('index');
	}

	function view($url  = null) {
		$project = array('Project' => $this->Project->config);
		if (empty($this->params['project']) && $url == null && $project['id'] != 1) {
			$project = $this->Project->findByUrl($url);
		}

		$this->set('project', $project);
	}

	function fork() {
		if ($this->Project->Repo->type == 'svn') {
			$this->Session->setFlash(__('You cannot fork an svn project yet',true));
			$this->redirect($this->referer());
		}

		if (!empty($this->params['form']['cancel'])) {
			$this->redirect(array('controller' => 'browser'));
		}

		if (!empty($this->data)) {
			$this->Project->create(array_merge(
				$this->Project->config,
				array(
					'user_id' => $this->Auth->user('id'),
					'fork' => $this->Auth->user('username'),
					'approved' => 1,
				)
			));
			if ($data = $this->Project->fork()) {
				if (empty($data['Project']['approved'])) {
					$this->Session->setFlash(__('Project is awaiting approval',true));
				} else {
					$this->Session->setFlash(__('Project was created',true));
				}
				$this->redirect(array(
					'fork' => $data['Project']['fork'],
					'controller' => 'browser', 'action' => 'index',
				));
			} else {
				$this->Session->setFlash(__('Project was NOT created',true));
			}
		}
	}

	function add() {

		$this->pageTitle = 'Project Setup';

		if (!empty($this->data)) {
			$this->Project->create(array(
				'user_id' => $this->Auth->user('id'),
				'username' => $this->Auth->user('username'),
				'approved' => $this->params['isAdmin']
			));
			if ($data = $this->Project->save($this->data)) {
				if (empty($data['Project']['approved'])) {
					$this->Session->setFlash(__('Project is awaiting approval',true));
				} else {
					$this->Session->setFlash(__('Project was created',true));
				}
				$this->redirect(array('project' => $data['Project']['url'], 'controller' => 'timeline', 'action' => 'index'));
			} else {
				$this->Session->setFlash(__('Project was NOT created',true));
			}
		}

		if (empty($this->data)) {
			$this->data = array_merge((array)$this->data, array('Project' => $this->Project->config));
			if (!empty($this->data['Project']['id'])) {
				unset($this->data['Project']['id'], $this->data['Project']['name'], $this->data['Project']['description']);
			}
		}

		$this->set('repoTypes', $this->Project->repoTypes());

		$this->set('messages', $this->Project->messages);

		$this->render('add');
	}

	function edit() {
		if ($this->params['isAdmin'] === false) {
			$this->redirect($this->referer());
		}

		$this->pageTitle = 'Update Project';

		if (!empty($this->data)) {
			$this->data['Project']['id'] = $this->Project->id;
			if ($data = $this->Project->save($this->data)) {
				$this->Session->setFlash('Project was updated');
			} else {
				$this->Session->setFlash('Project was NOT updated');
			}
		}

		$this->data = $this->Project->read();

		$this->set('repoTypes', $this->Project->repoTypes());

		$this->set('messages', $this->Project->messages);

		$this->render('edit');
	}

	function admin_index() {
		if ($this->Project->id !== '1' || $this->params['isAdmin'] === false) {
			$this->redirect($this->referer());
		}
		if ($this->params['isAdmin'] === true) {
			$this->paginate['conditions'] = array();
			$this->paginate['order'] = 'Project.id ASC';
		}
		$this->Project->recursive = 0;
		$this->set('projects', $this->paginate());
	}

	function admin_edit($id = null) {
		if (!$id) {
			$this->Session->setFlash('The project was invalid');
			$this->redirect(array('action' => 'index'));
		}

		$this->pageTitle = __('Project Admin',true);

		if ($this->Project->id !== '1' || $this->params['isAdmin'] === false) {
			$this->redirect($this->referer());
		}

		$this->Project->id = $id;

		if (!empty($this->data)) {
			if ($data = $this->Project->save($this->data)) {
				$this->Session->setFlash(__('Project was updated',true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Project was NOT updated',true));
			}
		}

		$this->data = $this->Project->read();

		$this->set('repoTypes', $this->Project->repoTypes());

		$this->set('messages', $this->Project->messages);

		$this->render('edit');
	}

	function admin_approve($id = null) {
		if ($id) {
			$this->Project->id = $id;
			if ($this->Project->save(array('approved' => 1))) {
				$this->Session->setFlash(__('The project was approved',true));
			} else {
				$this->Session->setFlash(__('The project was NOT approved',true));
			}
		} else {
			$this->Session->setFlash(__('The project was invalid',true));
		}
		$this->redirect(array('action' => 'index'));
	}

	function admin_reject($id = null) {
		if ($id) {
			$this->Project->id = $id;
			if ($this->Project->save(array('approved' => 0))) {
				$this->Session->setFlash(__('The project was rejected',true));
			} else {
				$this->Session->setFlash(__('The project was NOT rejected',true));
			}
		} else {
			$this->Session->setFlash(__('The project was invalid',true));
		}
		$this->redirect(array('action' => 'index'));
	}

	function admin_activate($id = null) {
		if ($id) {
			$this->Project->id = $id;
			if ($this->Project->save(array('active' => 1))) {
				$this->Session->setFlash(__('The project was activated',true));
			} else {
				$this->Session->setFlash(__('The project was NOT activated',true));
			}
		} else {
			$this->Session->setFlash(__('The project was invalid',true));
		}
		$this->redirect(array('action' => 'index'));
	}

	function admin_deactivate($id = null) {
		if ($id) {
			$this->Project->id = $id;
			if ($this->Project->save(array('active' => 0))) {
				$this->Session->setFlash(__('The project was deactivated',true));
			} else {
				$this->Session->setFlash(__('The project was NOT deactivated',true));
			}
		} else {
			$this->Session->setFlash(__('The project was invalid',true));
		}
		$this->redirect(array('action' => 'index'));
	}
}
?>