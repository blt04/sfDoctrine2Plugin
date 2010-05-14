<?php
use Symfony\Components\Console\Output\OutputInterface;

class sfDoctrineCliPrinter implements OutputInterface
{
  protected $formatter;
  protected $dispatcher;

  public function setFormatter($formatter)
  {
    $this->formatter = $formatter;
  }

  public function setDispatcher($dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($section, $message, $size, $style))));
  }

  /**
   * Writes a message to the output.
   *
   * @param string|array $messages The message as an array of lines of a single string
   * @param integer      $type     The type of output
   */
  public function write($messages, $type = 0)
  {
    $this->logSection("Doctrine", $messages);
    return $this;
  }

  public function setVerbosity($level)
  {
  }

  public function setDecorated($decorated)
  {
  }
}
