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

                if (!file_exists($file)) continue;

                $json = file_get_contents($file);
                if (!$json) continue;

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

            $cacheFile = $cacheDir . "/" . $date . ".json";

            if (file_exists($cacheFile)) {

                $json = file_get_contents($cacheFile);

                $posts = json_decode($json, true);

                if (!is_array($posts)) {
                    $posts = [];
                }

            } else {

                /**
                 * RSSは完全に外出し前提（ここでは扱わない）
                 * Renderで落ちる原因になるため削除
                 */

                $posts = [];
            }

            $title = $isToday ? "今日のニュース" : $date . " のニュース";
        }

        return view('posts.index', compact('posts', 'title', 'isToday'));
    }
}