<?php

/**
 * This validator makes sure the user password has not been set for awhile
 *
 * @package    sfDoctrineGuardExtraPlugin
 * @subpackage validatorPassword
 * @author     
 * @version    $Id$
 */
class sfValidatorPasswordExtra extends sfValidatorBase
{

  /**
   *
   * @param array $options
   * @param array $messages
   */
  protected function configure($options = array(), $messages = array())
  {
    /**
     * This is how many passwords back to check, any passwords past the reuse
     * max will be ignored and can be used again
     */
    $this->addOption('password_reuse_max', 8);

    /**
     * This is the model that stores the previously used passwords.
     */
    $this->addOption('model', 'sfGuardUserPassword');

    /**
     * This is required, you MUST pass a sfGuardUser object
     */
    $this->addRequiredOption('sf_user');
  }

  /**
   * Main logic that checks the database for previously used passwords
   *
   * @param mixed $value
   * @return mixed The cleaned value
   * @throws sfValidatorError
   */
  protected function doClean($value)
  {
    if (is_array($value))
    {
      if (!empty($value['password']))
      {
        $valueArray = $value;
        $value = $valueArray['password'];
      }
      else
      {
        throw new sfException('It appears you are using this as a post validator and do not have a "password" field for me to check =(');
      }
    }

    $sf_user = $this->getOption('sf_user');
    
    if ($sf_user instanceof myUser)
      throw new sfException('Must be sfGuardUser class, try ->getGuardUser()');

    if (sfConfig::get('app_sf_guard_extra_plugin_password_reuse_super_admin', false))
    {
      if ($sf_user->getIsSuperAdmin())
        if (isset($valueArray))
          return $valueArray;
        else
          return $value;
    }

    // check previous passwords
    $query = Doctrine::getTable($this->getOption('model'))->createQuery()
        ->where('user_id = ?', $sf_user->getId())->limit($this->getOption('password_reuse_max'));
    $results = $query->execute();

    foreach ($results as $result)
    {
      if ($result->checkPassword($value))
      {
        throw new sfValidatorError($this, 'Password has been found, you cannot reuse this password');
      }
    }

    // if we got this far, then we need to check the current password
    if ($sf_user->checkPassword($value))
      throw new sfValidatorError($this, 'Password has already been set, please try again');

    /**
     * Since all went fine we should add the password into the database
     */
    $sfGuardUserPassword = new sfGuardUserPassword();
    $sfGuardUserPassword->setUserId($sf_user->getId());
    $sfGuardUserPassword->setPassword($value);
    $sfGuardUserPassword->save();

    if (isset($valueArray))
      return $valueArray;
    else
      return $value;
  }

}