@php

$cacheDir = storage_path('app/news_cache');
if (!file_exists($cacheDir)) mkdir($cacheDir,0777,true);

$year = request()->get('year');
$date = request()->get('date', date('Y-m-d'));

$isToday = (!$year && $date == date('Y-m-d'));

$posts = [];
$title = "";


/*
|--------------------------------------------------------------------------
| 年ハイライト
|--------------------------------------------------------------------------
*/

if ($year) {

    $files = glob($cacheDir."/$year-*.json");

    foreach ($files as $file) {

        $data = json_decode(file_get_contents($file), true);

        foreach ($data as $p) {
            $posts[] = $p;
        }
    }

    shuffle($posts);
    $posts = array_slice($posts,0,30);

    $title = $year."年 ハイライト";
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

            if (isset($rss->channel->item)) {

                foreach ($rss->channel->item as $item) {

                    $desc = (string)$item->description;
                    $link = (string)$item->link;

                    preg_match('/<img.+src=["\'](.+?)["\']/', $desc, $m);
                    $img = $m[1] ?? null;

                    $items[] = [

                        'title'=>(string)$item->title,
                        'link'=>$link,
                        'desc'=>strip_tags($desc),
                        'img'=>$img,
                        'site'=>parse_url($link, PHP_URL_HOST),
                        'date'=>$date

                    ];
                }
            }

            if (isset($rss->entry)) {

                foreach ($rss->entry as $item) {

                    $desc = (string)$item->summary;
                    $link = (string)$item->link['href'];

                    preg_match('/<img.+src=["\'](.+?)["\']/', $desc, $m);
                    $img = $m[1] ?? null;

                    $items[] = [

                        'title'=>(string)$item->title,
                        'link'=>$link,
                        'desc'=>strip_tags($desc),
                        'img'=>$img,
                        'site'=>parse_url($link, PHP_URL_HOST),
                        'date'=>$date

                    ];
                }
            }
        }

        shuffle($items);
        $posts = array_slice($items,0,10);

        file_put_contents($cacheFile,json_encode($posts));
    }

    $title = $isToday ? "今日のニュース" : $date." のニュース";
}

@endphp


<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>

<style>

body{
margin:0;
background:#f6f7fb;
font-family:-apple-system,BlinkMacSystemFont,sans-serif;
}

/* header */

.header{
background:white;
padding:14px 18px;
display:flex;
align-items:center;
gap:15px;
box-shadow:0 2px 10px rgba(0,0,0,.05);
position:sticky;
top:0;
z-index:10;
}

.hamburger{
font-size:22px;
cursor:pointer;
}

.title{
font-size:18px;
font-weight:600;
cursor:pointer;
}


/* sidebar */

.sidebar{
position:fixed;
left:-300px;
top:0;
width:280px;
height:100%;
background:white;
box-shadow:0 0 30px rgba(0,0,0,.2);
transition:.3s;
z-index:100;
padding:15px;
overflow:auto;
}

.sidebar.open{
left:0;
}

.overlay{
position:fixed;
top:0;
left:0;
right:0;
bottom:0;
background:rgba(0,0,0,.4);
display:none;
z-index:50;
}

.overlay.show{
display:block;
}

.search{
width:100%;
padding:10px;
border:1px solid #ddd;
border-radius:8px;
margin-bottom:15px;
}

.year{
display:block;
padding:8px;
text-decoration:none;
color:#333;
border-radius:6px;
}

.year:hover{
background:#f1f3f7;
}


/* cards */

.container{
max-width:900px;
margin:20px auto;
padding:0 10px;
}

.card{
background:white;
margin-bottom:14px;
border-radius:12px;
overflow:hidden;
box-shadow:0 2px 8px rgba(0,0,0,.05);
}

.row{
display:flex;
}

.thumb{
width:160px;
height:110px;
object-fit:cover;
background:#eee;
}

.content{
padding:12px;
flex:1;
}

.date{
font-size:11px;
color:#2563eb;
margin-bottom:4px;
}

.news-title{
font-weight:600;
margin-bottom:5px;
}

.news-title a{
text-decoration:none;
color:#111;
}

.desc{
font-size:13px;
color:#555;
}

.site{
font-size:11px;
color:#888;
margin-top:6px;
}

</style>

</head>
<body>


<div class="header">

<div class="hamburger" onclick="openMenu()">☰</div>

<div class="title" onclick="location.href='/'">
{{ $title }}
</div>

</div>


<div class="overlay" id="overlay" onclick="closeMenu()"></div>


<div class="sidebar" id="sidebar">

<input
class="search"
placeholder="検索 (Ctrl+F)"
onkeyup="searchPosts(this.value)"
>

<h4>年別</h4>

@for($y=date('Y');$y>=2010;$y--)
<a class="year" href="?year={{ $y }}">
{{ $y }}年
</a>
@endfor

</div>


<div class="container">

@foreach($posts as $post)

<div class="card post">

<div class="row">

<img class="thumb"
src="{{ $post['img'] ?? 'https://placehold.co/300x200' }}"
>

<div class="content">

@if($isToday)
<div class="date">
{{ $post['date'] }}
</div>
@endif

<div class="news-title">
<a href="{{ $post['link'] }}" target="_blank">
{{ $post['title'] }}
</a>
</div>

<div class="desc">
{{ $post['desc'] }}
</div>

<div class="site">
{{ $post['site'] }}
</div>

</div>
</div>
</div>

@endforeach

</div>



<script>

function openMenu(){
document.getElementById("sidebar").classList.add("open")
document.getElementById("overlay").classList.add("show")
}

function closeMenu(){
document.getElementById("sidebar").classList.remove("open")
document.getElementById("overlay").classList.remove("show")
}

function searchPosts(keyword){

keyword = keyword.toLowerCase()

document.querySelectorAll(".post").forEach(card=>{

card.style.display =
card.innerText.toLowerCase().includes(keyword)
? "block"
: "none"

})

}

document.addEventListener("keydown",e=>{

if(e.ctrlKey && e.key==="f"){
e.preventDefault()
openMenu()
document.querySelector(".search").focus()
}

})

</script>

</body>
</html>