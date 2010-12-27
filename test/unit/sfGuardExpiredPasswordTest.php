<?php
/**
 * @author
 * @package    sfGurardExpiredPassword
 * @subpackage unit test
 * @version    $Id$
 */
include dirname(__FILE__).'/../bootstrap/bootstrap.php';

$databaseManager = new sfDatabaseManager($configuration);
$conn = Doctrine::getConnectionByTableName('sfGuardUserPassword');

$t = new lime_test();
$user = $context->getUser();
$sfGuardUser = Doctrine::getTable('sfGuardUser')->findOneByUsername('user');
$user->signIn($sfGuardUser);

$t->comment('Settings related to this test');
$t->info('password_expiration_date: '.sfConfig::get('app_sf_guard_extra_plugin_password_expiration_date'));

// let's change the user password created_at date to make sure it's expired
$result = Doctrine::getTable('sfGuardUserPassword')->createQuery()->where('user_id = ?',$sfGuardUser->getId())->orderBy('created_at DESC')->fetchOne();

// password should not be expired
$result->setCreatedAt(date("Y-m-d H:i:s"));
$result->save();
$t->is(false, Doctrine::getTable('sfGuardUserPassword')->isPassExpired($sfGuardUser->getId()),'->isPassExpired()');

// pass expired
$result->setCreatedAt('2000-01-01 00:00:00');
$result->save();
$t->is(true, Doctrine::getTable('sfGuardUserPassword')->isPassExpired($sfGuardUser->getId()),'->isPassExpired()');