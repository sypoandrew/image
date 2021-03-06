<?php
namespace Sypo\Image\Console\Commands;

use Illuminate\Console\Command;
use Sypo\Image\Models\Image;
use Symfony\Component\Console\Helper\ProgressBar;

class PlaceholderImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sypo:image:placeholder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process items without an image, use library image or default to placeholder image';

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
		$products = $l->get_products_without_images();
		
        $progressBar = new ProgressBar($this->output, $products->count());
		
		foreach($products as $product){
			#Handle image placeholder
			$l->handlePlaceholderImage($product);
			$progressBar->advance();
		}
		
		$l->create_report();
		
		$progressBar->finish();
    }
}
