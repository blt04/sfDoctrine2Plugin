<?php

$em = $this->getEntityManager();

$admin = new \Entities\User();
$admin->isActive = true;
$admin->username = 'admin';
$admin->password = 'changeme';

$jwage = new \Entities\User();
$jwage->isActive = true;
$jwage->username = 'jwage';
$jwage->password = 'changeme';

$profile = new \Entities\Profile();
$profile->isActive = true;
$profile->firstName = 'Jonathan';
$profile->lastName = 'Wage';
$jwage->profile = $profile;
$profile->user = $jwage;

$group = new \Entities\Group();
$group->name = 'Admin';
$group->addUsers($jwage);
$group->addUsers($admin);