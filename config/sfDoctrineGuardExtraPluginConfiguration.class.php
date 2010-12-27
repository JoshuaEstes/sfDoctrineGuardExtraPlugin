<?php

/**
 * sfDoctrineGuardExtraPlugin configuration.
 * 
 * @package     sfDoctrineGuardExtraPlugin
 * @subpackage  config
 * @author      
 * @version     $Id$
 */
class sfDoctrineGuardExtraPluginConfiguration extends sfPluginConfiguration
{

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('user.failed_authentication', array($this, 'listenToFailedAuthentication'));
  }

  /**
   *
   * @param sfEvent $event
   */
  public function listenToFailedAuthentication(sfEvent $event)
  {
    $loginAttempt = new sfGuardLoginAttempt();
    $loginAttempt->ip_address = $this->getUserIP();
    $loginAttempt->host_name = $this->getUserHost();
    $loginAttempt->save();
  }

  /**
   * Function that returns the user's IP address. Also check to make sure user is not behind a proxy
   *
   * @return string IP address of user
   */
  private function getUserIP()
  {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
      return $_SERVER['REMOTE_ADDR'];
  }

  /**
   * returns the host name for the user
   *
   * @return sting
   */
  private function getUserHost()
  {
    return gethostbyaddr($this->getUserIP());
  }

}
