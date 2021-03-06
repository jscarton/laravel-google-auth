<?php namespace JScarton\LaravelGoogleAuth;

use Illuminate\Auth\AuthServiceProvider;


class LaravelGoogleAuthServiceProvider extends AuthServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('jscarton/laravel-google-auth', null, realpath(__DIR__.'/../../'));
        parent::boot();

        $this->app['auth']->extend('google', function($app) {
            return new GoogleAuthGuard(new GoogleUserProvider(), $app['session.store']);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $app = $this->app;

        $app['google-client'] = $app->share(function($app)
        {
            $client = new \Google_Client();
            $client->setClientId($app['config']->get('laravel-google-auth::clientId'));
            $client->setClientSecret($app['config']->get('laravel-google-auth::clientSecret'));
            $client->setRedirectUri($app['config']->get('laravel-google-auth::redirectUri'));
            $client->setScopes($app['config']->get('laravel-google-auth::scopes'));
            $client->setAccessType($app['config']->get('laravel-google-auth::access_type'));
            $client->setApprovalPrompt($app['config']->get('laravel-google-auth::approval_prompt'));

            return $client;
        });

        $app['router']->filter('google-finish-authentication', function($route, $request) use ($app) {
            return $app['auth']->finishAuthenticationIfRequired();
        });

    }
}
