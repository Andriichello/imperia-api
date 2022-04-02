<?php

namespace App\Providers;

use Google\Client;
use Google\Service\Drive;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

/**
 * Class GoogleDriveServiceProvider.
 */
class GoogleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('google-drive', function ($app, $config) {
                $client = new Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                $options = [];
            if (isset($config['teamDriveId'])) {
                $options['teamDriveId'] = $config['teamDriveId'];
            }

                $adapter = new GoogleDriveAdapter(new Drive($client), $config['folderId'], $options);

                return new Filesystem($adapter);
        });
    }
}
