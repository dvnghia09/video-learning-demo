{{-- Dải tiêu đề dùng chung cho các trang bên trong: $title, $subtitle (tuỳ chọn), $crumbs = [['Tên','url'], ...] (tuỳ chọn) --}}
@push('jsonld')
@php
    $__items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')]];
    foreach (($crumbs ?? []) as $__c) { $__items[] = ['@type' => 'ListItem', 'position' => count($__items) + 1, 'name' => $__c[0], 'item' => $__c[1]]; }
    $__items[] = ['@type' => 'ListItem', 'position' => count($__items) + 1, 'name' => $title, 'item' => url()->current()];
@endphp
{!! \App\Support\JsonLd::tag(['@type' => 'BreadcrumbList', 'itemListElement' => $__items]) !!}
@endpush
<div class="bg-gradient-to-b from-amber-100 to-cream border-b-2 border-amber-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <nav class="text-sm text-stone-700 mb-3 flex flex-wrap items-center gap-1.5" aria-label="breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-amber-700 transition">Trang chủ</a>
            @foreach(($crumbs ?? []) as $c)
                <span class="text-stone-300">/</span>
                <a href="{{ $c[1] }}" class="hover:text-amber-700 transition">{{ $c[0] }}</a>
            @endforeach
            <span class="text-stone-300">/</span>
            <span class="text-stone-700 font-medium truncate max-w-[60vw]">{{ $title }}</span>
        </nav>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-stone-900">{{ $title }}</h1>
        @isset($subtitle)<p class="mt-2 text-stone-700 text-lg max-w-2xl">{{ $subtitle }}</p>@endisset
    </div>
</div>
