<?php
namespace Leichti\Molweight;


use Illuminate\Support\ServiceProvider;


class MolweightServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Compound::class);
        
    }
}


?>