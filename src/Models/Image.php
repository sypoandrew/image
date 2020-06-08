<?php

namespace Sypo\Image\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Aero\Catalog\Models\Product;
use Aero\Catalog\Models\TagGroup;
use Aero\Common\Models\Image as AeroImage;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Sypo\Image\Models\EmailNotification;
use Mail;
use Sypo\Image\Mail\ImageReportMail;

class Image
{
    protected $library_files;
    protected $tag_groups;
    protected $handle_default_fallback = true;
    #delete all current images
	protected $clear_previous_image = false;
    protected $email_code;
    protected $image_report_rows;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
		$this->library_files = [];
		$files = File::files(storage_path('app/image_library/library/'));
		foreach($files as $file){
			$this->library_files[] = pathinfo($file)['basename'];
		}
		$this->tag_groups = self::get_tag_groups();
	}
    
    /**
     * Set handle_default_fallback indicator to use fallback default bottle image if no library image present
     *
     * @param bool $handle_default_fallback
     * @return void
     */
    public function set_handle_default_fallback(bool $handle_default_fallback)
    {
        $this->handle_default_fallback = $handle_default_fallback;
    }
    
	/**
     * Set clear_previous_image indicator to delete all current images
     *
     * @param bool $clear_previous_image
     * @return void
     */
    public function set_clear_previous_image(bool $clear_previous_image)
    {
		$this->clear_previous_image = $clear_previous_image;
    }
    
    /**
     * Get all required tag groups for image handling
     *
     * @return Aero\Catalog\Models\TagGroup
     */
    public static function get_tag_groups()
    {
		$groups = TagGroup::get();
		$tag_groups = [];
		foreach($groups as $g){
			$tag_groups[$g->name] = $g;
		}
		return $tag_groups;
    }

    /**
     * @param \Aero\Catalog\Models\Product $product
     * @return void
     */
    public function handlePlaceholderImage(\Aero\Catalog\Models\Product $product)
    {
		$image_src = null;
		$image_name = '';
		$wine_type = '';
		$colour = '';
		
		#check if we have one in the library
		# - first check LWIN7 with space
		$lwin7 = substr($product->model, 2, 7);
		#dd($lwin7);
		foreach($this->library_files as $filename){
			if(substr($filename, 0, 8) == $lwin7 . ' '){
				$image_name = $filename;
				break;
			}
		}
		#dd($image_name);
		
		if($image_name){
			$image_src = storage_path('app/image_library/library/'.$image_name);
			#Log::debug($product->model.' use library image - '.$image_src);
		}
		elseif($this->handle_default_fallback){
			#deduce image from the colour/type using the plain default images 
			
			$tag_group = $this->tag_groups['Wine Type'];
			$tag = $product->tags()->where('tag_group_id', $tag_group->id)->first();
			if($tag != null){
				$wine_type = $tag->name;
			}
			
			$tag_group = $this->tag_groups['Colour'];
			$tag = $product->tags()->where('tag_group_id', $tag_group->id)->first();
			if($tag != null){
				$colour = $tag->name;
			}
			
			if($wine_type == 'Sparkling'){
				if($colour == 'Rose'){
					$image_name = 'sparklingrose.png';
				}
				else{
					$image_name = 'sparkling.png';
				}
			}
			elseif($wine_type == 'Fortified'){
				$image_name = 'fortified.png';
			}
			elseif($colour == 'Red'){
				$image_name = 'red.png';
			}
			elseif($colour == 'Rose'){
				$image_name = 'rose.png';
			}
			elseif($colour == 'White' or $colour == 'Sweet White'){
				$image_name = 'white.png';
			}
			
			if($image_name){
				$image_src = storage_path('app/image_library/defaults/'.$image_name);
				#Log::debug($product->model.' use default image - '.$image_src);
			}
			else{
				#Log::warning($product->model.' unable to create from default image - '.$wine_type.' | '.$colour);
			}
		}
		#dd($image_src);
		
		if($image_src !== null){
			
			#delete the current placeholder
			if(!$this->handle_default_fallback and $this->clear_previous_image){
				$product->allImages()->delete();
                #dd($product->allImages()->count());
			}
			
			$this->createOrUpdateImage($product, $image_src);
		}
	}

    /**
     * @param \Aero\Catalog\Models\Product $product
     * @param string $src
     * @return void
     */
    protected function createOrUpdateImage(\Aero\Catalog\Models\Product $product, $src)
    {
        $image = null;
        $existing = null;
        $update = null;

        if (isset($src)) {
            $temp = tempnam(sys_get_temp_dir(), 'aero-product-image');

            $url = $src;

            try {
                $context = stream_context_create([
                    'http' => [
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
                    ],
                ]);

                $image = file_get_contents($url, false, $context);
            } catch (\Exception $e) {
                Log::warning("Error downloading image {$url}: {$e->getMessage()}");
                $image = null;
            }

            if ($image) {
                file_put_contents($temp, $image);

                $file = new UploadedFile($temp, basename($url));

                $type = $file->getMimeType();
                $hash = md5(file_get_contents($file->getRealPath()));

                $image = AeroImage::where('hash', $hash)->first();

                if (! $image) {
                    try {
                        [$width, $height] = getimagesize($file->getRealPath());

                        $name = $file->storePublicly('images/products', 'public');

                        $image = AeroImage::create([
                            'file' => $name,
                            'type' => $type,
                            'width' => $width,
                            'height' => $height,
                            'hash' => $hash,
                            'source' => $url,
                        ]);
                    } catch (\Exception $e) {
                        Log::warning("Error processing image {$url}: {$e->getMessage()}");
                        $image = null;
                    }
                }
            }

            unlink($temp);
        }

        $position = null;
        $attribute = null;

        $default = true;

        if ($image) {
            $existing = $product->allImages()->where('image_id', $image->id)->first();
        }

        if (! $existing && $image) {
            $position = $product->allImages()->count();

            /** @var $update \Aero\Catalog\Models\ProductImage */
            $update = $product->allImages()->create([
                'image_id' => $image->id,
                'default' => $default,
                'sort' => $position,
            ]);

            if ($attribute) {
                $update->attributes()->syncWithoutDetaching([$attribute->id => ['sort' => $position]]);
            }
        } elseif ($existing) {
            $attributes = [
                'default' => $default,
            ];

            $existing->update($attributes);

            $update = $existing;
        }

        if ($update) {
            $update->save();
        }
    }
	
	public function get_products_without_images($report_version = false){
		$lang = config('app.locale');
		if($report_version){
			$products = Product::select('products.*')->leftJoin('product_images', 'product_images.product_id', '=', 'products.id')->whereNull('product_images.product_id')->get();
			$collection = collect();
			foreach($products as $product){
				$wine_type = $product->tags()->join('tag_groups', 'tag_groups.id', '=', 'tags.tag_group_id')->where("tag_groups.name->{$lang}", 'Wine Type')->first();
				$colour = $product->tags()->join('tag_groups', 'tag_groups.id', '=', 'tags.tag_group_id')->where("tag_groups.name->{$lang}", 'Colour')->first();
				
				$collection->push([
				'id' => $product->id, 
				'model' => $product->model, 
				'name' => $product->name, 
				'active' => $product->active, 
				'wine_type' => ($wine_type) ? $wine_type->name : '',
				'colour' =>  ($colour) ? $colour->name : ''
				]);
			}
			return $collection;
		} else{
			return Product::select('products.*')->leftJoin('product_images', 'product_images.product_id', '=', 'products.id')->whereNull('product_images.product_id')->get();
		}
	}
	
	public function get_products_with_default_image($report_version = false){
		$lang = config('app.locale');
		$this->handle_default_fallback = false;
		$this->clear_previous_image = true;
		
		$sources = ['s/p/sparklingrose.png', 's/p/sparkling.png', 'f/o/fortified.png', 'r/e/red.png', 'r/o/rose.png', 'w/h/white.png'];
		
		if($report_version){
			$products = Product::select('products.id', 'products.model', "products.name->{$lang} AS product_name", 'products.active')->join('product_images', 'product_images.product_id', '=', 'products.id')->join('images', 'images.id', '=', 'product_images.image_id');
		} else{
			$products = Product::select('products.*')->join('product_images', 'product_images.product_id', '=', 'products.id')->join('images', 'images.id', '=', 'product_images.image_id');
		}
		
		$products = $products->where(function ($q) use ($sources) {
			foreach($sources as $s){
				$q->orWhere('images.source', 'like', '%'.$s);
			}
		});
		
		$products = $products->get();
		return $products;
	}
	
	public function create_report(){
		$products = false;
		if($this->handle_default_fallback){
			$this->email_code = 'missing_image_report';
			#create report on items with missing images
			$products = $this->get_products_without_images(true);
		}
		else{
			$this->email_code = 'replace_default_image_report';
			#create report on items with default images
			$products = $this->get_products_with_default_image(true);
		}
		$this->image_report_rows = $products->toArray();
		$this->saveCsv();
		
		if(setting('Image.image_report_send_email')){
			#only send the email notification once a day
			$report_sent = EmailNotification::where('code', $this->email_code)->whereDate('created_at', \Carbon\Carbon::today())->count();
			if(!$report_sent){
				if($products){
					$email = new ImageReportMail($products, $this->email_code);
					Mail::send($email);
				}
			}
		}
	}

    /**
     * @throws \League\Csv\CannotInsertRecord
     */
    protected function saveCsv(): void
    {
        $csv = \League\Csv\Writer::createFromPath(storage_path("app/{$this->email_code}.csv"), 'w+');
        $csv->insertOne(array_keys(\Illuminate\Support\Arr::first($this->image_report_rows)));
        $csv->insertAll($this->image_report_rows);
    }
}
