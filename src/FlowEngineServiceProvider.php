<?php

namespace Helvetitec\FlowEngine;

use Illuminate\Support\ServiceProvider;

class FlowEngineServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'helvetitec.flowengine.config');
  }

  public function boot()
  {
    if($this->app->runningInConsole()){
      $this->publishes([     
        __DIR__.'/../config/config.php' => config_path('helvetitec.flowengine.config.php'),
      ], 'helvetitec.flowengine.config');      
    }
  }
}
