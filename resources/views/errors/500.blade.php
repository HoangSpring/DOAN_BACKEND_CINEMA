<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Lỗi máy chủ</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-9xl font-black text-yellow-600 mb-4">500</h1>
        <h2 class="text-3xl font-bold mb-6">Lỗi hệ thống</h2>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">Hệ thống đang gặp sự cố tạm thời. Chúng tôi đang xử lý, vui lòng thử lại sau ít phút.</p>
        <a href="{{ url('/') }}" class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-8 rounded-lg transition-transform hover:scale-105">
            Quay về trang chủ
        </a>
    </div>
</body>
</html>
