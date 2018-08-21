<?php

namespace PtxDev\Swagger;

class SwaggerLumenServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->register(\SwaggerLume\ServiceProvider::class);

        $this->app->singleton('command.swagger-lume.generate', function () {
            return new GenerateDocsCommand();
        });
    }
}
