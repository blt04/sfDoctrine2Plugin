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
 * Convert Doctrine mapping information between various supported formats.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineConvertMappingTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineConvertMappingTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('from', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'The path or paths to the mapping information you are converting from.'),
      new sfCommandOption('to', null, sfCommandOption::PARAMETER_REQUIRED, 'The format to convert to.'),
      new sfCommandOption('dest', null, sfCommandOption::PARAMETER_REQUIRED, 'The path to write the converted mapping information.'),
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'convert-mapping';
    $this->briefDescription = 'Convert mapping information between various supported formats.';

    $this->detailedDescription = <<<EOF
The [doctrine:convert-mapping|INFO] task converts mapping information between various supported formats:

  [./symfony doctrine:convert-schema --from=/path/to/yml --to=xml --dest=/path=/path/to/xml|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!($options['from'] && $options['to'] && $options['dest']))
    {
      throw new InvalidArgumentException('You must include a value for all four options: --from, --to and --dest');
    }

    $em = $options['from'][0] == 'database' ? true:false;

    $opts = array('filter' => $options['from']);

    $keys = array('filter', 'extend', 'num-spaces');
    if (isset($options['to']) && $options['to'] == 'annotation')
    {
      $opts['extend'] = 'sfDoctrineActiveEntity';
      $opts['num-spaces'] = 2;
    }

    $args = $this->prepareDoctrineCliArguments($opts, $keys);

    // to-type
    $args[] = $options['to'];
    // dest-path
    $args[] = $options['dest'];

    $this->callDoctrineCli('orm:convert-mapping', $args, $em);
  }
}
