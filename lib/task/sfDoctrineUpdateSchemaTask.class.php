<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Update schema task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineUpdateSchemaTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineUpdateSchemaTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('dump-sql', null, sfCommandOption::PARAMETER_NONE, 'Whether to output the generated sql instead of executing'),
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'update-schema';
    $this->briefDescription = 'Update schema for current model';

    $this->detailedDescription = <<<EOF
The [doctrine:update-schema|INFO] updates schema for the current model:

  [./symfony doctrine:update-schema|INFO]

The task connects to the database and updates all the tables for your schema.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $keys = array('dump-sql');
    $args = $this->prepareDoctrineCliArguments($options, $keys);

    return $this->callDoctrineCli('orm:schema-tool:update', $args);
  }
}
