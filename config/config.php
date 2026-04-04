
<?php 
//define sabit tanımlar yani degeri değiştirilemez projenin veya dosyanın her yerinden ulaşılabilir.
define('GOOGLE_BOOKS_API_KEY', 'AIzaSyD4JT35wvnxBXCxgf82fnO0FvD6VDt-ol4');
define('GEMINI_API_KEY', 'AIzaSyCXLOI1ixq66CVtvrOYQ19KA8V9GIuE9uc');


//__DIR__ komutuda dosyanın tam yolunu verir noktada iki stringi birleştirmek için kullanılır.
define('CACHE_DIR', __DIR__ . '/../cache/');


// Cache süresi: 3600 saniye = 1 saat
// Yani aynı kitabı 1 saat içinde tekrar aratırsan API'ye gitmez, dosyadan okur
define('CACHE_TIME', 3600);

// Geliştirme modunda true yap - hataları ekranda gösterir
define('DEBUG_MODE', false);