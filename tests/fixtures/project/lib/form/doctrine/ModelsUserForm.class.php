<?php

/**
 * EntitiesUser form.
 *
 * @package    test
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id$
 */
class EntitiesUserForm extends BaseEntitiesUserForm
{
  public function configure()
  {
    $profile = $this->object->profile ? $this->object->profile:new \Entities\Profile();
    $this->object->profile = $profile;
    $profile->user = $this->object;

    $profileForm = new EntitiesProfileForm($this->em, $profile);
    $profileForm->useFields(array('firstName', 'lastName'));
    unset($profileForm['id']);

    $this->embedForm('profile', $profileForm);

    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
  }
}
