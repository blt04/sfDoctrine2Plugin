<?php
function cleanup()
{
  //sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/cache');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/log');

  @unlink(sfConfig::get('sf_data_dir').'/test.sqlite');
  @unlink(sfConfig::get('sf_config_dir').'/app.yml');
}

cleanup();
register_shutdown_function('cleanup');