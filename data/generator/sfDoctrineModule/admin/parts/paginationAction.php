  public function getPager($model)
  {
    $pager = $this->configuration->getPager($this->getEntityManagerFor($model), '<?php echo $this->getModelClass() ?>');
    $pager->setQuery($this->buildQuery());
    $pager->setPage($this->getPage());
    $pager->init();

    return $pager;
  }

  protected function setPage($page)
  {
    $this->getUser()->setAttribute('<?php echo $this->getModuleName() ?>.page', $page, 'admin_module');
  }

  protected function getPage()
  {
    return $this->getUser()->getAttribute('<?php echo $this->getModuleName() ?>.page', 1, 'admin_module');
  }

  protected function buildQuery()
  {
    $repositoryMethod = $this->configuration->getRepositoryMethod();
<?php if ($this->configuration->hasFilterForm()): ?>
    if (is_null($this->filters))
    {
      $this->filters = $this->configuration->getFilterForm($this->getFilters());
    }

    $this->filters->setRepositoryMethod($repositoryMethod);

    $query = $this->filters->buildQuery($this->getFilters());
<?php else: ?>
    $query = Doctrine::getTable('<?php echo $this->getModelClass() ?>')
      ->createQuery('a');

    if ($tableMethod)
    {
      $query = Doctrine::getTable('<?php echo $this->getModelClass() ?>')->$tableMethod($query);
    }
<?php endif; ?>

    $this->addSortQuery($query);

    $event = $this->dispatcher->filter(new sfEvent($this, 'admin.build_query'), $query);
    $query = $event->getReturnValue();

    return $query;
  }
