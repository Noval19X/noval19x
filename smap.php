<?php
// Fungsi untuk menghasilkan sitemap.xml
function generateSitemap() {
    // Dapatkan path dari direktori tempat script ini berada
    $directory = __DIR__;

    // URL dasar, sesuaikan dengan URL dari situs Anda
    $baseUrl = "https://" . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $directory);

    // Array untuk menyimpan semua URL dari file
    $urls = [];

    // Scan semua file di direktori dan subdirektori
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($rii as $file) {
        // Lewati folder yang tidak relevan seperti ".", "..", dan folder ".git"
        if ($file->isDir() || strpos($file->getPathname(), '/.') !== false) {
            continue;
        }

        // Ambil path dari file dan sesuaikan untuk URL
        $filePath = str_replace($directory, '', $file->getPathname());
        $filePath = str_replace(DIRECTORY_SEPARATOR, '/', $filePath); // Ganti backslash dengan slash
        $filePath = ltrim($filePath, '/'); // Hapus slash pertama

        // Tambahkan ke array URL jika file adalah HTML atau PHP
        if (preg_match('/\.(html|php)$/i', $filePath)) {
            $urls[] = $baseUrl . '/' . $filePath;
        }
    }

    // Buat konten sitemap.xml
    $sitemapContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemapContent .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $sitemapContent .= "  <url>\n";
        $sitemapContent .= "    <loc>$url</loc>\n";
        $sitemapContent .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n"; // Tambahkan tanggal sekarang sebagai lastmod
        $sitemapContent .= "    <changefreq>daily</changefreq>\n"; // Frekuensi perubahan bisa disesuaikan
        $sitemapContent .= "    <priority>0.5</priority>\n"; // Prioritas bisa disesuaikan
        $sitemapContent .= "  </url>\n";
    }

    $sitemapContent .= '</urlset>';

    // Simpan konten ke dalam file sitemap.xml di path yang sama dengan file ini
    $sitemapFilePath = $directory . '/sitemap.xml';
    if (file_put_contents($sitemapFilePath, $sitemapContent)) {
        echo "Sitemap.xml berhasil dibuat di: $sitemapFilePath";
    } else {
        echo "Gagal membuat sitemap.xml";
    }
}

// Panggil fungsi untuk membuat sitemap saat file ini dijalankan
generateSitemap();
?>
