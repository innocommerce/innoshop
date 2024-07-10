@extends('layouts.app')

@section('content')
  @if($page->enable_page_head)
      <x-front-breadcrumb type="page" :value="$page" />
  @endif

  @hookinsert('page.show.top')

  @if(isset($result))
    {!! $result !!}
  @else
    <div class="page-service-content">
      <div class="container">
        <div class="row">
          {!! $page->translation->content !!}
        </div>
      </div>
    </div>
  @endif

  @hookinsert('page.show.bottom')

@endsection
