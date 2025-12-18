@php use Illuminate\Support\Facades\Route as R; @endphp


<nav class="w-full">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="{{ url('/') }}" class="font-bold">AVLCI</a>

    <div class="flex items-center gap-4">
      {{-- Public links --}}
      <a href="{{ url('/') }}" class="text-sm font-semibold">Home</a>

      {{-- Auth-aware section without throwing RouteNotFoundException --}}
      @auth
        <span class="text-sm">Hi, {{ Auth::user()->name }}</span>

        @if (R::has('dashboard'))
          <a href="{{ route('dashboard') }}" class="text-sm font-semibold">Dashboard</a>
        @endif

        @if (R::has('logout'))
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button class="text-sm font-semibold text-red-600 hover:text-red-800">Logout</button>
          </form>
        @endif
      @endauth

      @guest
        @if (R::has('login'))
          <a href="{{ route('login') }}" class="text-sm font-semibold">Login</a>
        @endif
        @if (R::has('register'))
          <a href="{{ route('register') }}" class="text-sm font-semibold">Register</a>
        @endif
      @endguest
    </div>
  </div>
</nav>
