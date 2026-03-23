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

      // Export the migration    
      if(!class_exists('CreateFlowRunsTable')){     
        $this->publishes([
          __DIR__.'/../database/migrations/create_flow_runs_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()).'_create_flow_runs_table.php'),              
        ], 'helvetitec.flowengine.migrations');     
      }    
    }
  }
}
