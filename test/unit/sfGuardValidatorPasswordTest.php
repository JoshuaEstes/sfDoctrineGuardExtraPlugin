<?php
/**
 * @author
 * @package    sfGurardLoginAttempt
 * @subpackage unit test
 * @version    $Id$
 */
include dirname(__FILE__).'/../bootstrap/bootstrap.php';

$databaseManager = new sfDatabaseManager($configuration);
$conn = Doctrine::getConnectionByTableName('sfGuardUserPassword');

$t = new lime_test();
$user = $context->getUser(); // get myUser
$user->signIn(Doctrine::getTable('sfGuardUser')->findOneByUsername('admin')); // signin admin user

$validator = new sfValidatorPasswordExtra(array(
  'sf_user' => $user->getGuardUser()
));

$t->comment('Some settings that will be used');
$t->info('password_reuse_max: '.sfConfig::get('app_sf_guard_extra_plugin_password_reuse_max',8));

try
{
  // should already have password
  $validator->clean('admin');
  $t->fail();
}
catch (sfValidatorError $e)
{
  $t->pass();
}