<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;

use Storage;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tm:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download';

    /**
     * @var Client
     */
    protected $twitter;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->twitter = new Client([
            config('twitter.CONSUMER_KEY'),
            config('twitter.CONSUMER_SECRET'),
            config('twitter.ACCESS_TOKEN'),
            config('twitter.ACCESS_TOKEN_SECRET'),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = [
            'count'            => 200,
            //            'trim_user'        => false,
            'include_entities' => true,
            //            'exclude_replies'  => true,
        ];

        if (Storage::disk('local')->exists('since_id')) {
            $since_id = Storage::disk('local')->get('since_id');
            $options['since_id'] = $since_id;
        } else {
            $since_id = 0;
        }

        info('since_id start: ' . $since_id);

        $tweets = $this->twitter->get('statuses/home_timeline', $options);

        collect($tweets)->each(function ($tweet) use (&$since_id) {
            if ($since_id < $tweet->id) {
                $since_id = $tweet->id;
            }

            if (isset($tweet->retweeted_status)) {
                return;
            }

            if (empty($tweet->extended_entities)) {
                return;
            }

            $media = $tweet->extended_entities->media;
            foreach ($media as $medium) {
                if ($medium->type === 'photo') {
                    $this->photo($medium);
                } elseif ($medium->type === 'video') {
                    $this->video($medium);
                }
            }
        });

        Storage::disk('local')->put('since_id', $since_id);
        info('since_id end: ' . $since_id);
    }

    /**
     * 画像
     *
     * @param $medium
     */
    private function photo($medium)
    {
        $url = $medium->media_url_https;

        $this->download($url);
    }

    /**
     * 動画
     *
     * @param $medium
     */
    private function video($medium)
    {
        $variants = collect($medium->video_info->variants);

        $video = $variants->reject(function ($v) {
            return empty($v->bitrate);
        })->sort(function ($v) {
            return $v->bitrate;
        })->last();

        $url = $video->url;

        $this->download($url);
    }

    /**
     * ファイルをダウンロードして保存
     *
     * @param string $url
     */
    private function download(string $url)
    {
        info($url);

        /**
         * @var \mpyw\Cowitter\Media $responce
         */
        $responce = $this->twitter->getOut($url);

        $path = parse_url($url, PHP_URL_PATH);
        $file = pathinfo($path, PATHINFO_BASENAME);

        Storage::cloud()->put($file, $responce->getBinaryString());
    }
}
