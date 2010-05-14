<?php

/**
 * EntitiesGroup form base class.
 *
 * @package    test
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id$
 */
class BaseEntitiesGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'   => new sfWidgetFormInputHidden(array()),
      'name' => new sfWidgetFormInputText(array()),
    ));

    $this->setValidators(array(
      'id'   => new sfValidatorDoctrineChoice($this->em, array('model' => 'Entities\Group', 'column' => 'id', 'required' => false)),
      'name' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('models_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Entities\Group';
  }

}
