<?php

$database = true;
$app = 'frontend';
$fixtures = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$user = \Entities\User::findOneByUsername('jwage');
$t->is($user->username, 'jwage');