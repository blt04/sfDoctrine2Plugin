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
 * Check Doctrine version task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineVersionTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineVersionTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'version';
    $this->briefDescription = 'Check which version of Doctrine you are using';

    $this->detailedDescription = <<<EOF
The [doctrine:version|INFO] outputs which version of Doctrine you are using:

  [./symfony doctrine:version|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callDoctrineCli('--version');
  }
}
