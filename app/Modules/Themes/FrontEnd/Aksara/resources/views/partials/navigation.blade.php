<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="{{ url('/') }}">@filter('aksara.tagline','Aksara Tagline')</a>
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
      Menu
      <i class="fa fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav ml-auto">
        @foreach ( get_menu('primary') as $menu)
            <li class="nav-item">
                @if( !str_contains($menu['data']['url'],'http://') )
                <a class="nav-link" href="{{url($menu['data']['url'])}}">{{ $menu['data']['label'] }}</a>
                @else
                <a class="nav-link" href="{{$menu['data']['url']}}">{{ $menu['data']['label'] }}</a>
                @endif
            </li>
        @endforeach
      </ul>
      <div class="header-search active" id="header-search">
          <form action="{{url('/search')}}">
              <input type="text" name="query" placeholder="Ketik kata kunci pencarian Anda di sini." class="form-control">
              <button class="header-search__submit"><i class="fa fa-search"></i></button>
          </form>
      </div>
      <div class="search-toggle">
            <i class="fa fa-search"></i>
        </div>
        <div class="lang">
            @foreach(get_registered_locales() as $locale)
                @if(get_current_multibas_locale()==$locale['language_code'])
                    <div class="lang-active">
                @else
                    <div class="option">
                @endif
                @if(is_default_multibas_locale($locale['language_code']))
                    <a href='{{url('/')}}'><span class="flag-icon flag-icon-{{$locale['language_code']}}"></span></a>
                @else
                    <a href='{{url('/'.$locale['language_code'])}}'><span class="flag-icon flag-icon-{{$locale['language_code']}}"></span></a>
                @endif
                {{-- Close class flag --}}
                </div>
            @endforeach
        </div>
    </div>
  </div>
</nav>
