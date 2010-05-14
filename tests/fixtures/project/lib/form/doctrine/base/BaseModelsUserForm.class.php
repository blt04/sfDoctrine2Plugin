<?php

/**
 * EntitiesUser form base class.
 *
 * @package    test
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id$
 */
class BaseEntitiesUserForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'       => new sfWidgetFormInputHidden(array()),
      'isActive' => new sfWidgetFormInputCheckbox(array()),
      'username' => new sfWidgetFormInputText(array()),
      'password' => new sfWidgetFormInputText(array()),
    ));

    $this->setValidators(array(
      'id'       => new sfValidatorDoctrineChoice($this->em, array('model' => 'Entities\User', 'column' => 'id', 'required' => false)),
      'isActive' => new sfValidatorBoolean(array('required' => false)),
      'username' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'password' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('models_user[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Entities\User';
  }

}
