<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Illuminate\Support\Facades\Storage;

class WebScraping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web-scraping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $data = [];

        $client = new Client();
        $crawler = $client->request('GET', 'https://jo.opensooq.com/ar');

        $crawler->filter('.mainCatesCont ul li')->each(function ($liElement) use ($client, &$data) {

            $a = $liElement->filter('.subCatsCont h3 a')->first();
            $link = $a->link();

            $adsPage =   $client->click($link);

            $adsPage->filter("#gridPostListing li")->each(function ($liElement) use (&$data) {

                $innerData = [];
                if ($liElement->filter('.rectLiDetails')->count() > 0) {
                    $img = $liElement->filter('span img')->first()->attr('src');
                    $details = $liElement->filter('.rectLiDetails')->children();
                    $title = $details->eq(0)->text();
                    $price = $details->eq(1)->text();
                    $informations = $details->eq(2)->text();
                    $innerData['img'] = $img;
                    $innerData['title'] = $title;
                    $innerData['price'] = $price;
                    $innerData['informations'] = $informations;
                    $data[] = $innerData;
                }
            });
        });

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        Storage::disk('public')->put("ads.json", $data);



        return 0;
    }
}
