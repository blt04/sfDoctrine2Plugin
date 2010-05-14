  public function executeBatch(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    if (!$ids = $request->getParameter('ids'))
    {
      $this->getUser()->setFlash('error', 'You must at least select one item.');

      $this->redirect('@<?php echo $this->getUrlForAction('list') ?>');
    }

    if (!$action = $request->getParameter('batch_action'))
    {
      $this->getUser()->setFlash('error', 'You must select an action to execute on the selected items.');

      $this->redirect('@<?php echo $this->getUrlForAction('list') ?>');
    }

    if (!method_exists($this, $method = 'execute'.ucfirst($action)))
    {
      throw new InvalidArgumentException(sprintf('You must create a "%s" method for action "%s"', $method, $action));
    }

    if (!$this->getUser()->hasCredential($this->configuration->getCredentials($action)))
    {
      $this->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
    }

    $validator = new sfValidatorDoctrineChoice($this->getEntityManagerFor('<?php echo $this->getModelClass() ?>'), array('multiple' => true, 'model' => '<?php echo $this->getModelClass() ?>'));
    try
    {
      // validate ids
      $ids = $validator->clean($ids);

      // execute batch
      $this->$method($request);
    }
    catch (sfValidatorError $e)
    {
      $this->getUser()->setFlash('error', 'A problem occurs when deleting the selected items as some items do not exist anymore.');
    }

    $this->redirect('@<?php echo $this->getUrlForAction('list') ?>');
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
		$em = $this->getEntityManagerFor('<?php echo $this->getModelClass() ?>');
    $ids = $request->getParameter('ids');

		$qb = $em->createQueryBuilder();
    $records = $qb
			->select('a')
      ->from('<?php echo $this->getModelClass() ?>', 'a')
      ->where($qb->expr()->in('a.<?php echo $this->getPrimaryKeys(true) ?>', $ids))
      ->getQuery()
			->execute();

    foreach ($records as $record)
    {
      $record->delete();
    }

    $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    $this->redirect('@<?php echo $this->getUrlForAction('list') ?>');
  }
