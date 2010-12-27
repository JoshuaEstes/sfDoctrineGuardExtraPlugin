<?php
/**
 * @author
 * @package    sfGurardLoginAttempt
 * @subpackage unit test
 * @version    $Id$
 */
include dirname(__FILE__).'/../bootstrap/bootstrap.php';

$databaseManager = new sfDatabaseManager($configuration);
$conn = Doctrine::getConnectionByTableName('sfGuardLoginAttempt');

$t = new lime_test();

// display some current settings:
$t->info("login_attempts: ".sfConfig::get('app_sf_guard_extra_plugin_login_attempts'));
$t->info("lock_for: ".sfConfig::get('app_sf_guard_extra_plugin_lock_for')." seconds");
$t->info("lock_timeout: ".sfConfig::get('app_sf_guard_extra_plugin_lock_timeout')." seconds");

$t->is(false, Doctrine::getTable('sfGuardLoginAttempt')->isLockedOut('127.0.0.1'),"->isLockedOut()");

addFailedLogin();
$t->is(false, Doctrine::getTable('sfGuardLoginAttempt')->isLockedOut('127.0.0.1'),"->isLockedOut() after 1 failed login");

// oh no, someone hammered the web site! ;p
for($i=0;$i<10;$i++)
  addFailedLogin();

//$t->comment("Account locked until: ".Doctrine::getTable('sfGuardLoginAttempt')->isLockedOut('127.0.0.1'));
$t->is(true, Doctrine::getTable('sfGuardLoginAttempt')->isLockedOut('127.0.0.1'),"->isLockedOut() after plus 10 failed logins");

/**
 * Function used to add a failed login to the database
 */
function addFailedLogin()
{
  $tmp = new sfGuardLoginAttempt();
  $tmp->ip_address = '127.0.0.1';
  $tmp->host_name = 'localhost';
  $tmp->save();
}