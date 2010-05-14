<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelDoctrine adds a panel to the web debug toolbar with Doctrine information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfWebDebugPanelDoctrine.class.php 11205 2008-08-27 16:24:17Z fabien $
 */
class sfWebDebugPanelDoctrine extends sfWebDebugPanel
{
  /**
   * Get the title/icon for the panel
   *
   * @return string $html
   */
  public function getTitle()
  {
    if ($logs = $this->getSqlLogs())
    {
      return '<img src="'.$this->webDebug->getOption('image_root_path').'/database.png" alt="SQL queries" /> '.count($logs);
    }
  }

  /**
   * Get the verbal title of the panel
   *
   * @return string $title
   */
  public function getPanelTitle()
  {
    return 'SQL queries';
  }

  /**
   * Get the html content of the panel
   *
   * @return string $html
   */
  public function getPanelContent()
  {
    return '
      <div id="sfWebDebugDatabaseLogs">
        <ol>'.implode("\n", $this->getSqlLogs()).'</ol>
      </div>
    ';
  }

  /**
   * Hook to allow the loading of the Doctrine webdebug toolbar with the rest of the panels
   *
   * @param sfEvent $event 
   * @return void
   */
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('db', new self($event->getSubject()));
  }

  protected function getLogs()
  {
    return $this->webDebug->getLogger()->getLogs();
  }

  /**
   * Builds the sql logs and returns them as an array.
   *
   * @return array
   */
  protected function getSqlLogs()
  {
    $logs = $this->getLogs();

    $html = array();
    foreach ($logs as $log)
    {
      if (false !== strpos($log['message'], 'query : '))
      {
        $sql = str_replace('query : ', '', $log['message']);
        $sql = $this->formatSql(htmlspecialchars($sql, ENT_QUOTES, sfConfig::get('sf_charset')));
        $html[] = sprintf('
          <li>
            <p class="sfWebDebugDatabaseQuery">%s</p>
          </li>',
          $sql
        );
      }
    }
    return $html;
  }
}