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

.header{
background:white;
padding:14px 18px;
display:flex;
align-items:center;
gap:15px;
box-shadow:0 2px 10px rgba(0,0,0,.05);
position:sticky;
top:0;
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

.sidebar{
position:fixed;
left:-300px;
top:0;
width:280px;
height:100%;
background:white;
transition:.3s;
padding:15px;
overflow:auto;
}

.sidebar.open{ left:0; }

.overlay{
position:fixed;
inset:0;
background:rgba(0,0,0,.4);
display:none;
}

.overlay.show{ display:block; }

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
}

.row{ display:flex; }

.thumb{
width:160px;
height:110px;
object-fit:cover;
background:#eee;
}

.content{ padding:12px; flex:1; }

.news-title a{
text-decoration:none;
color:#111;
font-weight:600;
}

.desc{ font-size:13px; color:#555; }

.site{ font-size:11px; color:#888; }

</style>

</head>
<body>

<div class="header">
<div class="hamburger" onclick="openMenu()">☰</div>
<div class="title" onclick="location.href='/'">{{ $title }}</div>
</div>

<div class="overlay" id="overlay" onclick="closeMenu()"></div>

<div class="sidebar" id="sidebar">
<h4>年別</h4>

@for($y=date('Y'); $y>=2010; $y--)
<a href="?year={{ $y }}">{{ $y }}年</a><br>
@endfor
</div>

<div class="container">

@foreach($posts as $post)

<div class="card post">
<div class="row">

<img class="thumb"
src="{{ $post['img'] ?? 'https://placehold.co/300x200' }}">

<div class="content">

@if($isToday)
<div class="site">{{ $post['date'] }}</div>
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
</script>

</body>
</html>