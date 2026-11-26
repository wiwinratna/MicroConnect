{{-- Shared page frame for login, change password and registration. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    <title>@yield('title')</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/minect-theme/theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/minect-theme/forms.css') }}" rel="stylesheet">
    <link href="{{ asset('css/minect-theme/auth.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="mn-auth">
    @yield('content')

    <script src="{{ asset('js/password-toggle.js') }}"></script>
    @stack('scripts')
</body>
</html>
