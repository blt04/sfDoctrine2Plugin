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
 * Generate Doctrine proxy classes
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn <russ@eatmymonkeydust.com>
 * @version    SVN: $Id: sfDoctrineVersionTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineGenerateProxiesTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'generate-proxies';
    $this->briefDescription = 'Generate the Doctrine proxy classes';

    $this->detailedDescription = <<<EOF
The [doctrine:version|INFO] generates the Doctrine proxy clases using your configured proxy directory

  [./symfony doctrine:generate-proxies|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callDoctrineCli('orm:generate-proxies');
  }
}
