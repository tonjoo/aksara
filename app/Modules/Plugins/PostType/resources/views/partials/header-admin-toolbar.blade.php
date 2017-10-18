<style>
	html {
		margin-top: 32px !important;
		position: relative;
	}
	* html body {
		margin-top: 32px !important;
	}
</style>
<link rel="stylesheet" type="text/css" href="../dist/css/adminbar.min.css">

<div id="aksara-adminbar" class="adminbar">
	<ul class="adminbar__menu">
		<li class="adminbar__item">
			<a href="{{url('/admin')}}"><i class="ti-dashboard"></i> <span> Dashboard </span></a>
		</li>
        @if( is_single() )
		<li class="adminbar__item">
			<a href="../index.php"><i class="ti-pencil"></i> <span> Edit Post </span></a>
		</li>
        @endif
	</ul>
	<ul class="adminbar__secondary">
		<li class="adminbar__dropdown adminbar__item">
			<a href="#" class="adminbar__dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true">
                <i class="ti-user"></i>
                <span class="name">Username</span>
			</a>
            <ul class="adminbar__dropdown-menu">
            @foreach($adminMenus as $adminMenu)
                @foreach ($adminMenu as $menu)
                    <li><a href="{{ $menu['url'] }}"><i class="m-r-10 {{ $menu['class'] }}"></i>{{ $menu['title'] }}</a></li>
                @endforeach
            @endforeach
            </ul>

		</li>
	</ul>
</div>
