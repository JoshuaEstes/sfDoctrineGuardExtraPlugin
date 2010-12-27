<?php
/**
 * Checks the sfGuardAuthExtra actions
 *
 * @todo Add tests ;p
 *
 * @author
 * @package    sfDoctrineGuardExtra
 * @subpackage function test
 * @version    $Id$
 */
require_once dirname(__FILE__).'/../bootstrap/bootstrap.php';

$b = new sfTestFunctional(new sfBrowser());

$b->info('test')
  ->get('/')
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end();