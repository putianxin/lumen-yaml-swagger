<?php

namespace PtxDev\Swagger;

class GenerateDocsCommand extends \SwaggerLume\Console\GenerateDocsCommand
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Regenerating docs');
        Generator::generateDocs();
    }
}
