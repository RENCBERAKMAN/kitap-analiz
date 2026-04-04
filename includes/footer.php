<?php
/*
 * includes/footer.php
 *
 * Her sayfanın en sonunda require_once ile dahil edilir.
 *
 * ── NEDEN JS DOSYASINI BURADA YÜKLEYİZ? ───────────────────────
 * <script> tagını <head> yerine </body>'den hemen önce koyuyoruz.
 *
 * <head>'de yüklenirse:
 *   Tarayıcı HTML'i parse etmeden önce JS indirir ve çalıştırır.
 *   JS "getElementById('searchInput')" dediğinde element henüz
 *   DOM'da yok → null döner → hata.
 *
 * </body> öncesinde yüklenirse:
 *   Tarayıcı önce tüm HTML'i parse eder, DOM hazır olur.
 *   Sonra JS indirilir ve çalışır → getElementById güvenle çalışır.
 *
 * Ek olarak: DOMContentLoaded event ile de bekliyoruz (app.js içinde).
 * Bu çift güvence sağlar.
 * ────────────────────────────────────────────────────────────── */
?>

<!-- ══ SITE FOOTER ══ -->
<footer class="site-footer" role="contentinfo">
  <div class="footer-emblem" aria-hidden="true">❧</div>
  <div class="footer-name">Kitap Analizi</div>
  <div class="footer-stack">
    Google Books API
    <span class="footer-divider">·</span>
    Gemini AI
    <span class="footer-divider">·</span>
    PHP cURL
  </div>
</footer>

<!--
  Ana JavaScript dosyası — DOM hazır olduktan sonra yükle
  defer veya body sonu: ikisi de çalışır, biz body sonu tercih ediyoruz
-->
<script src="/kitap-analiz/assets/js/app.js"></script>

</body>
</html>