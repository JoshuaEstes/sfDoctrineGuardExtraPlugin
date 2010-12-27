<?php

class guardEmailexpiredusersTask extends sfBaseTask
{
  /**
   * Array that holds user id's of who has had mail sent to them
   * @var array
   */
  protected $_emailed_users = array();

  protected function configure()
  {
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('days',null,  sfCommandOption::PARAMETER_REQUIRED,'How many days till the users password expires',15),
    ));

    $this->namespace        = 'guard';
    $this->name             = 'email-expired-users';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [guard:email-expired-users|INFO] task will send an email to all users to let
them know that their password is about to expire.

Call it with:

  [php symfony guard:email-expired-users|INFO]

By default it will email users who's password will expire in 15 days or less. You
can edit this setting my passing the days option like so:

  [php symfony guard:email-expired-users --days="5"|INFO]

EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    // load the partial helper
    $context = sfContext::createInstance($this->configuration);
    $context->getConfiguration()->loadHelpers(array('Partial'));
    
    $userQuery = Doctrine_Query::create()
      ->from('sfGuardUserPassword sgup')
      ->innerJoin('sgup.User u')
      ->orderBy('sgup.created_at DESC');
    $results = $userQuery->execute();
    foreach ($results as $result)
    {
      if ($this->hasEmailedUser($result->getUserId()))
        continue;
      
      $this->getMailer()->composeAndSend(
        sfConfig::get('app_sf_guard_extra_plugin_password_expiration_email_from'),
        $result->getUser()->getEmailAddress(),
        'Password Expired',
        get_partial('sfGuardAuthExtra/expiredPasswordEmail')
      );

      $this->userEmailed($result->getUserId());
    }
  }

  protected function userEmailed($user_id)
  {
    $this->_emailed_users[$user_id] = true;
  }

  protected function hasEmailedUser($user_id)
  {
    return isset($this->_emailed_users[$user_id]);
  }
}
