<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $cacheDir = storage_path('app/news_cache');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $year = $request->get('year');
        $date = $request->get('date', date('Y-m-d'));

        $isToday = (!$year && $date === date('Y-m-d'));

        $posts = [];
        $title = "";

        /*
        |--------------------------------------------------------------------------
        | 年別
        |--------------------------------------------------------------------------
        */
        if ($year) {

            $files = glob($cacheDir . "/$year-*.json") ?: [];

            foreach ($files as $file) {

                $json = file_get_contents($file);
                $data = json_decode($json, true);

                if (!is_array($data)) continue;

                foreach ($data as $p) {
                    if (is_array($p)) {
                        $posts[] = $p;
                    }
                }
            }

            shuffle($posts);
            $posts = array_slice($posts, 0, 30);

            $title = $year . "年 ハイライト";
        }

        /*
        |--------------------------------------------------------------------------
        | 今日 / 日別
        |--------------------------------------------------------------------------
        */
        else {

            $cacheFile = "$cacheDir/$date.json";

            if (file_exists($cacheFile)) {

                $posts = json_decode(file_get_contents($cacheFile), true);
                $posts = is_array($posts) ? $posts : [];

            } else {

                $feeds = [
                    "https://news.yahoo.co.jp/rss/topics/top-picks.xml",
                    "https://gigazine.net/news/rss_2.0/",
                    "https://www.itmedia.co.jp/rss/2.0/news_bursts.xml",
                    "https://www.gizmodo.jp/index.xml"
                ];

                $items = [];

                foreach ($feeds as $feed) {

                    $xml = @file_get_contents($feed);
                    if (!$xml) continue;

                    $rss = @simplexml_load_string($xml);
                    if (!$rss) continue;

                    // RSS
                    if (!empty($rss->channel->item)) {

                        foreach ($rss->channel->item as $item) {

                            $link = (string)($item->link ?? '');

                            if (!$link) continue;

                            $items[] = [
                                'title' => (string)$item->title,
                                'link'  => $link,
                                'desc'  => strip_tags((string)$item->description),
                                'img'   => null,
                                'site'  => parse_url($link, PHP_URL_HOST) ?? '',
                                'date'  => $date,
                            ];
                        }
                    }

                    // Atom
                    if (!empty($rss->entry)) {

                        foreach ($rss->entry as $item) {

                            $link = (string)($item->link['href'] ?? '');

                            if (!$link) continue;

                            $items[] = [
                                'title' => (string)$item->title,
                                'link'  => $link,
                                'desc'  => strip_tags((string)$item->summary),
                                'img'   => null,
                                'site'  => parse_url($link, PHP_URL_HOST) ?? '',
                                'date'  => $date,
                            ];
                        }
                    }
                }

                shuffle($items);
                $posts = array_slice($items, 0, 10);

                @file_put_contents(
                    $cacheFile,
                    json_encode($posts, JSON_UNESCAPED_UNICODE)
                );
            }

            $title = $isToday ? "今日のニュース" : $date . " のニュース";
        }

        return view('posts.index', compact('posts', 'title', 'isToday'));
    }
}