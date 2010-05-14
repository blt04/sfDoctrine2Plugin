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
 * Create schema task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineCreateSchemaTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineCreateSchemaTask extends sfDoctrineBaseTask
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

    $this->aliases = array('doctrine-insert-sql', 'doctrine:insert-sql');
    $this->namespace = 'doctrine';
    $this->name = 'create-schema';
    $this->briefDescription = 'Inserts SQL for current model';

    $this->detailedDescription = <<<EOF
The [doctrine:insert-sql|INFO] task creates database tables:

  [./symfony doctrine:insert-sql|INFO]

The task connects to the database and creates tables for all the
[lib/model/doctrine/*.php|COMMENT] files.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $keys = array('dump-sql');
    $args = $this->prepareDoctrineCliArguments((array)$options, $keys);

    return $this->callDoctrineCli('orm:schema-tool:create', $args);
  }
}
