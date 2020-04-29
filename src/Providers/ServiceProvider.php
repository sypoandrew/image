<?php

namespace Sypo\Image\Providers;

use Aero\Admin\AdminModule;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Common\Facades\Settings;
use Aero\Common\Settings\SettingGroup;

class ServiceProvider extends ModuleServiceProvider
{

    protected $commands = [
        'Sypo\Image\Console\Commands\PlaceholderImage',
        'Sypo\Image\Console\Commands\UpdateDefaultImage',
    ];

    public function register(): void 
    {
        AdminModule::create('Image')
            ->title('Image')
            ->summary('Aero Commerce product image processing')
            ->routes(__DIR__ .'/../../routes/admin.php')
            ->route('admin.modules.image');
        
        $this->commands($this->commands);
    }
	
    public function boot(): void 
    {
        parent::boot();
		
        Settings::group('Image', function (SettingGroup $group) {
            $group->boolean('enabled')->default(true);
            $group->string('image_report_send_to_email')->default('andrew@sypo.uk');
            $group->string('image_report_send_from_email')->default('sales@vinquinn.com');
            $group->string('image_report_send_from_name')->default('VinQuinn Sales');
        });
		
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		$this->loadViewsFrom(__DIR__ . '/../../resources/views/', 'image');
    }
}