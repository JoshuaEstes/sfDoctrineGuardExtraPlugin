<?php

/**
 *
 * @package    sfDoctrineGuardExtra
 * @subpackage filter
 * @version    $Id$
 */
class sfGuardSecurityExtraFilter extends sfFilter
{

  /**
   *
   * @param sfFilterChain $filterChain 
   */
  public function execute($filterChain)
  {
    // check to make sure the plugin is enabled, plugin is disabled by default
    if (!sfConfig::get('app_sf_guard_extra_plugin_enabled', false))
    {
      $filterChain->execute();

      return;
    }

    // no point in check if user is logged in =\ or if the are going to the locked out module/action
    if (!$this->getContext()->getUser()->isAuthenticated() ||
      (sfConfig::get('app_sf_guard_extra_plugin_locked_out_module') == $this->getContext()->getModuleName() &&
      sfConfig::get('app_sf_guard_extra_plugin_locked_out_action') == $this->getContext()->getActionName()))
    {
      $this->checkIfLockedOut();
    }

    // check to see if user is trying to login
    if (!$this->getContext()->getUser()->isAuthenticated() &&
      sfConfig::get('sf_login_module') == $this->getContext()->getModuleName() &&
      sfConfig::get('sf_login_action') == $this->getContext()->getActionName() &&
      'POST' == $this->getContext()->getRequest()->getMethod()
    )
    {
      /**
       * NOTE: in the future it may be possible to use the form, bind the values,
       *       and check to see if it is valid here. This is worth looking into
       *       so the plugin doesn't need to modify any other files.
       */
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(sprintf('"%s" ("%s") failed trying to login', $this->getUserIP(), $this->getUserHost()))));
      }
    }

    // check to see if the user is logged in if their password will expire soon
    if ($this->getContext()->getUser()->isAuthenticated())
    {
      $this->checkIfPassExpired();
    }

    // user has access to continue
    $filterChain->execute();
  }

  /**
   * This function checks if the user is locked out of the site. It also notifies
   * the dispatcher of the 'user.locked_out' event.
   */
  private function checkIfLockedOut()
  {
    if (Doctrine::getTable('sfGuardLoginAttempt')->isLockedOut($this->getUserIP()))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(sprintf('IP %s (%s) has been locked out of the web site.', $this->getUserIP(), $this->getUserHost()))));
      }

      $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'user.locked_out', array('user_ip' => $this->getUserIP(), 'user_host' => $this->getUserHost())));

      $this->forwardToLockedOut();
    }
  }

  /**
   * This functions checks to see if the users password has expired, if it has it
   * will notify the dispatcher and log the event. Will also forward user to the
   * password expired module/action
   */
  private function checkIfPassExpired()
  {
    if (Doctrine::getTable('sfGuardUserPassword')->isPassExpired($this->getContext()->getUser()->getGuardUser()->getId()))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(sprintf('Password has expired for username "%s"', $this->getContext()->getUser()->getGuardUser()->getUsername()))));
      }

      $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'user.password_expired', array('sf_user' => $this->getContext()->getUser())));

      $this->forwardToPasswordExpired();
    }
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

  /**
   * forward to the module/action to show if user is locked out of the site
   */
  private function forwardToLockedOut()
  {
    $this->getContext()->getController()->forward(sfConfig::get('app_sf_guard_extra_plugin_locked_out_module', 'sfGuardAuthExtra'), sfConfig::get('app_sf_guard_extra_plugin_locked_out_action', 'lockedOut'));

    throw new sfStopException();
  }

  /**
   * forward to the module/action to show the user their password has expired
   */
  private function forwardToPasswordExpired()
  {
    $this->getContext()->getController()->forward(sfConfig::get('app_sf_guard_extra_plugin_password_expiration_module', 'sfGuardAuthExtra'), sfConfig::get('app_sf_guard_extra_plugin_password_expiration_action', 'passwordExpired'));

    throw new sfStopException();
  }

}