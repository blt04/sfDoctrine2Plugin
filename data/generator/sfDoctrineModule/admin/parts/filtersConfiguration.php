  public function getFilterForm($filters)
  {
    $class = $this->getFilterFormClass();

    return new $class($this->em, $filters, $this->getFilterFormOptions());
  }

  public function getFilterFormOptions()
  {
    return array();
  }

  public function getFilterDefaults()
  {
    return array();
  }
