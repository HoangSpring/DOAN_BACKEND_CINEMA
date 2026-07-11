<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Không tìm thấy trang</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-9xl font-black text-red-600 mb-4">404</h1>
        <h2 class="text-3xl font-bold mb-6">Trang không tồn tại</h2>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển. Vui lòng kiểm tra lại đường dẫn.</p>
        <a href="{{ url('/') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition-transform hover:scale-105">
            Quay về trang chủ
        </a>
    </div>
</body>
</html>
