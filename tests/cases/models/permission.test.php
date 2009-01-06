<?php
/* SVN FILE: $Id$ */
/* Permission Test cases generated on: 2008-10-17 12:10:29 : 1224273329*/
App::import('Model', 'Permission');
class TestPermission extends Permission {

	var $useDbConfig = 'test_suite';
	var $cacheSources = false;
}

class PermissionTest extends CakeTestCase {

	var $fixtures = array(
		'app.project', 'app.permission', 'app.user', 'app.wiki',
		'app.timeline', 'app.comment', 'app.ticket', 'app.version',
		'app.tag', 'app.tags_tickets', 'app.commit'
	);

	function start() {
		parent::start();
		Configure::write('Content', array(
			'base' => TMP . 'tests' . DS,
			'git' => TMP . 'tests' . DS . 'git' . DS,
			'svn' => TMP . 'tests' . DS . 'svn' . DS ,
		));
		$this->__projects = array(
			'One' => array(
				'id' => 1,
				'url' => 'chaw',
				'repo' => array(
					'type' => 'git',
					'path' => TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'chaw.git',
					'working' => TMP . 'tests' . DS . 'git' . DS . 'working' . DS . 'chaw'
				)
			),
			'Two' => array(
				'id' => 2,
				'url' => 'project_two',
				'repo' => array(
					'type' => 'git',
					'path' => TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'project_two.git',
					'working' => TMP . 'tests' . DS . 'git' .DS . 'working' . DS . 'project_two'
				)
			),
			'Fork' => array(
				'id' => 3,
				'url' => 'project_two',
				'fork' => 'bob',
				'project_id' => 3,
				'repo' => array(
					'type' => 'git',
					'path' => TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'forks' . DS . 'bob' . DS . 'project_two.git',
					'working' => TMP . 'tests' . DS . 'git' .DS . 'working' . DS . 'forks' . DS . 'bob' . DS . 'project_two'
				)
			)
		);
	}

	function end() {
		parent::end();
		$Cleanup = new Folder(TMP . 'tests/git');
		if ($Cleanup->pwd() == TMP . 'tests/git') {
			$Cleanup->delete();
		}
	}

	function testSaveFile() {
		Configure::write('Project', $this->__projects['One']);
		$Permission = new TestPermission();

		$data['Permission']['fine_grained'] = "
		[chaw:/refs/heads/master]
		gwoo = r
		@chaw-developers = rw

		[chaw:/wiki/add]
		gwoo = rw

		[chaw:/test/deny/all]
		* =

		[chaw:/tickets/add]
		gwoo = rw

		[project_two:/test/override]
		gwoo = rw

		[groups]
		chaw-developers = gwoo, bob, tom";

		$Permission->saveFile($data);

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'permissions.ini'));


		Configure::write('Project', $this->__projects['Two']);
		$Permission = new TestPermission();

		$data['Permission']['fine_grained'] = "
		[/refs/heads/master]
		gwoo = r
		@project_two-developers = rw

		[/test/override]
		gwoo = r

		[groups]
		project_two-developers = gwoo, nate, larry";

		$Permission->saveFile($data);

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'project_two.git' . DS . 'permissions.ini'));


	}

	function testRules() {
		Configure::write('Project', $this->__projects['One']);
		$Permission = new TestPermission();

		$result = $Permission->rules();
//		pr($result);
		$expected = array(
			'chaw' => array(
				'/refs/heads/master' => array(
					'gwoo' => 'r',
					'@chaw-developers' => 'rw'
				),
				'/wiki/add' => array(
					'gwoo' => 'rw'
				),
				'/test/deny/all' => array(
					'*' => ''
				),
				'/tickets/add' => array(
					'gwoo' => 'rw'
				),
			),
			'project_two' => array(
				'/test/override' => array(
					'gwoo' => 'rw'
				)
			),
			'groups' => array(
				'chaw-developers' => array('gwoo', 'bob', 'tom')
			)
		);
		$this->assertEqual($result, $expected);

		Configure::write('Project', $this->__projects['Two']);
		$Permission = new TestPermission();

		$result = $Permission->rules();
//		pr($result);
		$expected = array(
			'project_two' => array(
				'/refs/heads/master' => array(
					'gwoo' => 'r',
					'@project_two-developers' => 'rw'
				),
				'/test/override' => array(
					'gwoo' => 'rw'
				)
			),
			'groups' => array(
				'project_two-developers' => array('gwoo', 'nate', 'larry'),
				'chaw-developers' => array('gwoo', 'bob', 'tom')
			)
		);
		$this->assertEqual($result, $expected);
	}

	function testGroups() {
		Configure::write('Project', $this->__projects['One']);
		$Permission = new TestPermission();

		$result = $Permission->groups();
		$expected = array(
			array('Group' =>
				array(
					'name' => 'chaw-developers',
					'users' => array('gwoo', 'bob', 'tom')
				)
			)
		);
		$this->assertEqual($result, $expected);
	}

	function testCheck() {
		Configure::write('Project', $this->__projects['One']);
		$Permission = new TestPermission();

		$this->assertTrue($Permission->check("/refs/heads/master", array('group' => 'chaw-developers', 'access' => 'rw', 'default' => false)));

		//gwoo is in chaw-developers which has rw on /refs/heads/master
		$this->assertTrue($Permission->check("/refs/heads/master", array('user' => 'gwoo', 'access' => 'w')));

		//bob is in chaw-developers which has rw on /refs/heads/master
		$this->assertTrue($Permission->check("/refs/heads/master", array('user' => 'bob', 'access' => 'w')));

		//larry is NOT in chaw-developers which has rw on /refs/heads/master
		$this->assertFalse($Permission->check("/refs/heads/master", array('user' => 'larry', 'access' => 'w')));

		$this->assertTrue($Permission->check("/tickets/add", array('user' => 'gwoo', 'access' => 'r')));

		$this->assertTrue($Permission->check("/refs/heads/master", array('user' => 'gwoo', 'access' => 'r')));

		$this->assertFalse($Permission->check("/test/deny/all", array('user' => 'gwoo', 'access' => 'r')));


		Configure::write('Project', $this->__projects['Two']);
		$Permission = new TestPermission();

		$this->assertTrue($Permission->check("/refs/heads/master", array('group' => 'project_two-developers', 'access' => 'rw')));

		//gwoo is in project_two-developers which has rw on /refs/heads/master
		$this->assertTrue($Permission->check("/refs/heads/master", array('user' => 'gwoo', 'access' => 'w')));

		//bob is NOT in project_two-developers which has rw on /refs/heads/master
		$this->assertFalse($Permission->check("/refs/heads/master", array('user' => 'bob', 'access' => 'w')));

		//larry is in project_two-developers which has rw on /refs/heads/master
		$this->assertTrue($Permission->check("/refs/heads/master", array('user' => 'larry', 'access' => 'w')));

		$this->assertTrue($Permission->check("/refs/heads/master", array('user' => 'gwoo', 'access' => 'r')));

		$this->assertTrue($Permission->check("/test/override", array('user' => 'gwoo', 'access' => 'r')));
	}

	function testCrudCheck() {
		Configure::write('Project', $this->__projects['Two']);
		$Permission = new TestPermission();

		$data['Permission']['fine_grained'] = "
		[wiki]
		gwoo = cru

		[tickets]
		gwoo = rw

		[browser]
		gwoo = r

		[versions]
		gwoo = crud";


		$Permission->saveFile($data);

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'permissions.ini'));

		$this->assertTrue($Permission->check("wiki", array('user' => 'gwoo', 'access' => array('w', 'c'))));
		$this->assertTrue($Permission->check("wiki", array('user' => 'gwoo', 'access' => array('w', 'd'))));

		$this->assertTrue($Permission->check("tickets", array('user' => 'gwoo', 'access' => array('w', 'c'))));
		$this->assertTrue($Permission->check("tickets", array('user' => 'gwoo', 'access' => array('w', 'd'))));

		$this->assertTrue($Permission->check("browser", array('user' => 'gwoo', 'access' => array('r', 'r'))));
		$this->assertFalse($Permission->check("browser", array('user' => 'gwoo', 'access' => array('w', 'd'))));

		$this->assertTrue($Permission->check("versions", array('user' => 'gwoo', 'access' => array('w', 'c'))));
		$this->assertTrue($Permission->check("versions", array('user' => 'gwoo', 'access' => array('w', 'd'))));

	}

	function testGroupsBetter() {
		Configure::write('Project', $this->__projects['Two']);
		$Permission = new TestPermission();

		$data['Permission']['fine_grained'] = "
		[/refs/heads/master]
		gwoo = r
		@project_two-developers = rw

		[wiki]
		@project_two-developers = rw

		[groups]
		project_two-developers = gwoo, nate, larry";

		$Permission->saveFile($data);

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'project_two.git' . DS . 'permissions.ini'));

		$this->assertTrue($Permission->check("wiki", array('user' => 'gwoo', 'access' => 'rw')));
	}

	function testSomeMoreChecks() {
		Configure::write('Project', $this->__projects['Two']);
		$Permission = new TestPermission();

		$data['Permission']['fine_grained'] = "";
		$Permission->saveFile($data);

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'project_two.git' . DS . 'permissions.ini'));

		$this->assertTrue($Permission->check("browser", array('user' => 'gwoo', 'access' => array('r', 'r'), 'default' => true)));

		$this->assertTrue($Permission->check("browser", array('user' => false, 'access' => array('r', 'r'), 'default' => true)));

	}

	function testForkOverride() {
		Configure::write('Project', $this->__projects['Two']);
		$Parent = new TestPermission();

		$data['Permission']['fine_grained'] = "
		[/refs/heads/master]
		gwoo = rw
		";

		$this->assertTrue($Parent->saveFile($data));

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'project_two.git' . DS . 'permissions.ini'));

		$result = $Parent->rules();

		Configure::write('Project', $this->__projects['Fork']);
		$Fork = new TestPermission();

		$data['Permission']['fine_grained'] = "
		[/refs/heads/master]
		bob = r
		gwoo = r
		";

		$Fork->saveFile($data);

		$this->assertTrue(file_exists(TMP . 'tests' . DS . 'git' . DS . 'repo' . DS . 'forks' . DS . 'bob' . DS . 'project_two.git' . DS . 'permissions.ini'));

		$result = $Fork->rules();

		$expected = array('project_two' => array(
			'/refs/heads/master' => array(
				'bob' => 'r',
				'gwoo' => 'rw'
			),
			'/test/override' => array(
				'gwoo' => 'rw'
			)
		));
		$this->assertEqual($result, $expected);
	}

	function testUserPermissions() {
		Configure::write('Project', $this->__projects['One']);
		$Permission = new TestPermission();
		$Permission->create(array(
			'project_id' => 1,
			'user_id' => 1,
			'group' => 'user',
		));
		$data['Permission']['fine_grained'] = "
		[wiki]
		gwoo = cru

		[tickets]
		@user = rw

		[browser]
		gwoo = r

		[versions]
		gwoo = crud";

		$Permission->saveFile($data);

		$this->assertTrue($Permission->check("tickets", array('group' => 'user', 'access' => 'rw', 'default' => false)));

		$this->assertFalse($Permission->check("tickets", array('group' => 'team', 'access' => 'rw', 'default' => false)));
		$this->assertFalse($Permission->check("tickets", array('user' => 'gwoo', 'group' => 'team', 'access' => 'rw', 'default' => false)));
	}

	function testPermissionGroup() {
		$Permission = new TestPermission();
		$Permission->create(array(
			'project_id' => 1,
			'user_id' => 1,
			'group' => 'user',
		));
		$Permission->save();

		$Permission->create(array(
			'project_id' => 1,
			'user_id' => 2,
			'group' => 'admin',
		));
		$Permission->save();

		$result = $Permission->group(array(
			'project' => 1,
			'user' => 1,
		));
		$this->assertEqual($result, 'user');


		$result = $Permission->group(1, 2);
		$this->assertEqual($result, 'admin');
	}
}
?>