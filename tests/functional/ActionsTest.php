<?php

$app = 'frontend';
$fixtures = true;
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$b = new sfTestFunctional(new sfBrowser());
$b->setTester('doctrine', 'sfTesterDoctrine');

$info = array(
	'models_user' => array(
		'isActive' => true,
		'username' => 'jonwage2',
		'password' => 'newpassword',
		'profile' => array(
			'firstName' => 'Jon',
			'lastName' => 'Wage'
		)
	)
);

$checkInfo = array(
	'isActive' => true,
	'username' => 'jonwage2',
	'password' => md5('newpassword'),
);

$b->
  get('/users')->
  click('jwage')->
  click('Save', $info)->
	with('form')->begin()->
		hasErrors(false)->
	end()->
  with('doctrine')->begin()->
    check($em, 'Entities\User', $checkInfo)->
  end()->
	with('response')->begin()->
		isRedirected(true)->
		followRedirect()->
	end()
;

$info = array(
	'models_user' => array(
		'isActive' => false,
		'username' => 'jwage',
		'password' => 'changeme',
		'profile' => array(
			'firstName' => 'J',
			'lastName' => 'Wage'
		)
	)
);

$checkInfo = array(
	'isActive' => 0,
	'username' => 'jwage',
	'password' => md5('changeme')
);

$b->
  click('Back to list')->
	click('New')->
	  click('Save', $info)->
		with('form')->begin()->
			hasErrors(false)->
		end()->
	  with('doctrine')->begin()->
	    check($em, 'Entities\User', $checkInfo)->
			check($em, 'Entities\Profile', $info['models_user']['profile'])->
	  end()->
		with('response')->begin()->
			isRedirected(true)->
			followRedirect()->
		end()
	;