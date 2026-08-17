<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Videocrat</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @viteReactRefresh
    @vite('resources/js/app.jsx')
</head>

<body>
<div id="app"></div>
@if (!app()->environment('local'))
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
@endif
</body>
</html>
