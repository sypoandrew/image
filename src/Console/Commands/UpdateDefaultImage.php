<?php
namespace Sypo\Image\Console\Commands;

use Illuminate\Console\Command;
use Sypo\Image\Models\Image;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdateDefaultImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sypo:image:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update default placeholder images with library image';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$l = new Image;
		$products = $l->get_products_with_default_image();
		
        $progressBar = new ProgressBar($this->output, $products->count());
		
		foreach($products as $product){
			#Attempt to update default image
			$l->handlePlaceholderImage($product);
			$progressBar->advance();
		}
		
		$l->create_report();
		
		$progressBar->finish();
    }
}
