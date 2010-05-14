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
 * Check if Doctrine is properly configured for a production environment
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn <russ@eatmymonkeydust.com>
 * @version    SVN: $Id: sfDoctrineCreateSchemaTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineCheckSettings extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'check-settings';
    $this->briefDescription = 'Checks if Doctrine is properly configured for production environment';

    $this->detailedDescription = <<<EOF
The [doctrine:check-settings|INFO] task checks if Doctrine is properly
configured for production environment

  [./symfony doctrine:check-settings|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callDoctrineCli('orm:ensure-production-settings');
  }
}
