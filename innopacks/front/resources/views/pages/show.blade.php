@extends('layouts.app')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getKeywords())

@section('content')
  @if($page->show_breadcrumb)
      <x-front-breadcrumb type="page" :value="$page" />
  @endif

  @hookinsert('page.show.top')

  @if(isset($result))
    {!! $result !!}
  @else
    <div class="page-content">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body p-4 p-lg-5">
                <h1 class="text-center mb-4">{{ $page->translation->title }}</h1>
                <div class="content">
                  {!! $page->translation->content !!}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  @hookinsert('page.show.bottom')
@endsection

@push('styles')
<style>
.page-content {
  min-height: 70vh;
  background-color: #f8f9fa;
}

.page-content .card {
  border: none;
  border-radius: 15px;
}

.page-content .content {
  font-size: 16px;
  line-height: 1.8;
  color: #333;
  max-width: 1200px;
  margin: 0 auto;
}

.page-content h2 {
  color: #2c3e50;
  margin-bottom: 1.5rem;
  font-weight: 600;
  font-size: 1.8rem;
}

.page-content h3 {
  color: #34495e;
  margin: 2rem 0 1rem;
  font-weight: 500;
  font-size: 1.4rem;
}

.page-content p {
  margin-bottom: 1.2rem;
}

.page-content ul {
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

.page-content ul li {
  margin-bottom: 0.5rem;
  position: relative;
  padding-left: 0.5rem;
}

.page-content ul li:before {
  content: "";
  position: absolute;
  left: -1.2rem;
  top: 0.7rem;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #6c5ce7;
}

@media (max-width: 768px) {
  .page-content .card-body {
    padding: 1.5rem !important;
  }
  
  .page-content .content {
    font-size: 15px;
  }

  .page-content h2 {
    font-size: 1.6rem;
  }

  .page-content h3 {
    font-size: 1.3rem;
  }
}
</style>
@endpush
