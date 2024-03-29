<?php namespace Jp\Twilio;

use Illuminate\Support\ServiceProvider;

class TwilioServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

        // Register manager for usage with the Facade.
        $this->app->singleton('twilio', function () {
            $config = \Config::get('services.twilio');
            if (!array($config)) {
                throw new \Exception('Twilio: Invalid configuration loaded on services.php.');
            }
            return new Twilio($config);
        });

        // $this->app['twilio'] = $this->app->share(function($app)
        // {
        //     $config = \Config::get('services.twilio');
        //     if (!array($config)) {
        //         throw new \Exception('Twilio: Invalid configuration.');
        //     }
        //     return new Twilio($config);
        // });

        // // Register Twilio Test SMS Command
        // $this->app['twilio.sms'] = $this->app->share(function($app) {
        //     return new Commands\TwilioSmsCommand();
        // });

        // // Register Twilio Test Call Command
        // $this->app['twilio.call'] = $this->app->share(function($app) {
        //     return new Commands\TwilioCallCommand();
        // });

        // $this->commands(
        //     'twilio.sms',
        //     'twilio.call'
        // );

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('twilio');
	}

}
