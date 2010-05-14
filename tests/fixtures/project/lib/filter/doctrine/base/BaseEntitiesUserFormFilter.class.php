<?php

/**
 * EntitiesUser filter form base class.
 *
 * @package    test
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
class BaseEntitiesUserFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'isActive' => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'username' => new sfWidgetFormFilterInput(array()),
      'password' => new sfWidgetFormFilterInput(array()),
    ));

    $this->setValidators(array(
      'isActive' => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'username' => new sfValidatorPass(array('required' => false)),
      'password' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entities_user_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Entities\User';
  }

  public function getFields()
  {
    return array(
      'id'       => 'Number',
      'isActive' => 'Boolean',
      'username' => 'Text',
      'password' => 'Text',
    );
  }
}
