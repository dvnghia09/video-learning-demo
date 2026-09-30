@php $__fav = ($site ?? \App\Models\Setting::bag())->faviconUrl(); @endphp
@if($__fav)
<link rel="icon" href="{{ $__fav }}">
<link rel="apple-touch-icon" href="{{ $__fav }}">
@endif
