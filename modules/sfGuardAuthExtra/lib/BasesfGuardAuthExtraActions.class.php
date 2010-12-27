<?php

/**
 * Base actions for the sfDoctrineGuardExtraPlugin sfGuardAuthExtra module.
 * 
 * @package     sfDoctrineGuardExtraPlugin
 * @subpackage  sfGuardAuthExtra
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BasesfGuardAuthExtraActions extends sfActions
{
  /**
   *
   * @param sfWebRequest $request 
   */
  public function executeLockedOut(sfWebRequest $request)
  {

  }

  /**
   *
   * @param sfWebRequest $request 
   */
  public function executePasswordExpired(sfWebRequest $request)
  {

  }
}
