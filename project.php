<?php
// Matikan pesan error Notice untuk script ini
error_reporting(E_ALL & ~E_NOTICE);

// Fungsi untuk menampilkan petunjuk penggunaan
function displayInstructions() {
    echo "<div class='instructions'>
        <h2>Petunjuk Penggunaan</h2>
        <ol>
            <li>Masukkan URL utama yang ingin digenerate di kolom \'URL\'.</li>
            <li>Upload file template (.txt) yang berisi struktur HTML yang ingin digunakan.</li>
            <li>Upload file .txt untuk masing-masing input:
                <ul>
                    <li>Nama brand (masukkan list brand di file .txt dengan setiap brand pada baris terpisah)</li>
                    <li>Meta description (daftar meta description dengan urutan sesuai brand)</li>
                    <li>Deskripsi domain (daftar deskripsi domain dengan urutan sesuai brand)</li>
                    <li>Judul (daftar judul dengan urutan sesuai brand)</li>
                    <li>URL konten (daftar URL konten dengan urutan sesuai brand)</li>
                </ul>
            </li>
            <li>Masukkan \'Link Tujuan\' untuk setiap tombol \'Daftar\' pada template.</li>
            <li>Klik tombol \'Generate\' untuk membuat file <strong>index.php</strong> di dalam folder yang dibuat berdasarkan nama brand.</li>
            <li>Periksa folder yang dibuat di path yang sama dengan tool untuk melihat hasilnya.</li>
        </ol>
        <h3>Script Khusus untuk Template</h3>
        <p>Gunakan script placeholder berikut pada file template Anda:</p>
        <ul>
            <li><code>{brand}</code> : Diganti dengan nama brand yang diupload.</li>
            <li><code>{tittle}</code> : Diganti dengan judul yang diupload.</li>
            <li><code>{deskripsi}</code> : Diganti dengan deskripsi domain yang diupload.</li>
            <li><code>{url}</code> : Diganti dengan URL yang diinputkan pada form.</li>
            <li><code>{content_url}</code> : Diganti dengan URL konten yang diupload.</li>
            <li><code>{canonical}</code> : Diganti dengan canonical URL yang di-generate otomatis.</li>
            <li><code>{og:url}</code> : Diganti dengan Open Graph URL yang di-generate otomatis.</li>
            <li><code>{og:description}</code> : Diganti dengan meta description yang diupload.</li>
            <li><code>{link_daftar}</code> : Diganti dengan \'Link Tujuan\' yang diinputkan pada form.</li>
            <li><code>{random_number}</code> : Diganti dengan angka acak puluhan juta.</li>
        </ul>
    </div>";
}

// Fungsi untuk membuat angka acak puluhan juta
function generateRandomNumber() {
    return number_format(rand(10000000, 99999999), 0, ',', '.');
}

// Fungsi untuk membersihkan nama folder dan URL
function sanitizeName($string) {
    // Ubah ke lowercase, hilangkan spasi, dan ganti spasi atau karakter khusus dengan "-"
    return strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', trim($string)));
}

// Fungsi untuk menghapus double slash "//" di URL, kecuali pada protokol (http://, https://)
function sanitizeUrl($url) {
    // Pengecekan untuk protokol http:// atau https://
    if (preg_match('/^https?:\/\//', $url, $matches)) {
        $protocol = $matches[0];  // Ambil protokol
        $urlWithoutProtocol = substr($url, strlen($protocol));  // Hapus protokol dari URL
        // Hapus double slash di bagian lain dari URL selain protokol
        $urlWithoutProtocol = preg_replace('#/+#', '/', $urlWithoutProtocol);
        return $protocol . $urlWithoutProtocol;  // Gabungkan kembali protokol dengan URL yang sudah di-clean
    } else {
        // Hapus double slash jika tidak ada protokol http:// atau https://
        return preg_replace('#/+#', '/', $url);
    }
}

// Cek apakah form sudah di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $url = rtrim($_POST['url'], '/'); // Menghapus slash di akhir URL jika ada
    $linkDaftar = $_POST['link_daftar'];

    // Baca semua data dari file yang di-upload dan simpan ke dalam array
    $brands = file($_FILES['brand']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $titles = file($_FILES['title']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $metaDescriptions = file($_FILES['meta_description']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $domainDescriptions = file($_FILES['domain_description']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $contentUrls = file($_FILES['content_url']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $template = file_get_contents($_FILES['template']['tmp_name']);

    // Pastikan semua file memiliki jumlah baris yang sama
    $totalEntries = count($brands);
    if ($totalEntries == count($titles) && $totalEntries == count($metaDescriptions) && $totalEntries == count($domainDescriptions) && $totalEntries == count($contentUrls)) {
        
        // Loop untuk setiap brand di dalam daftar
        for ($i = 0; $i < $totalEntries; $i++) {
            $brand = trim($brands[$i]);
            $title = trim($titles[$i]);
            $metaDescription = trim($metaDescriptions[$i]);
            $domainDescription = trim($domainDescriptions[$i]);
            $contentUrl = trim($contentUrls[$i]);

            // Membersihkan nama brand untuk dijadikan nama folder dan URL
            $cleanBrand = sanitizeName($brand);

            // Buat folder berdasarkan nama brand yang sudah di-clean (di path yang sama dengan tools ini)
            if (!file_exists($cleanBrand)) {
                mkdir($cleanBrand, 0777, true);
            }

            // Buat URL unik untuk setiap folder yang sudah di-clean
            $uniqueUrl = sanitizeUrl($url . '/' . $cleanBrand);

            // Ganti placeholder dalam template dengan data yang diunggah
            $templateModified = str_replace('{brand}', $brand, $template);
            $templateModified = str_replace('{tittle}', $title, $templateModified);
            $templateModified = str_replace('{deskripsi}', $domainDescription, $templateModified);
            $templateModified = str_replace('{url}', $uniqueUrl, $templateModified);
            $templateModified = str_replace('{content_url}', $contentUrl, $templateModified); // Memasukkan URL konten tanpa pengecekan
            $templateModified = str_replace('{canonical}', $uniqueUrl, $templateModified);
            $templateModified = str_replace('{og:url}', $uniqueUrl, $templateModified);
            $templateModified = str_replace('{og:description}', $metaDescription, $templateModified);
            $templateModified = str_replace('{link_daftar}', sanitizeUrl($linkDaftar), $templateModified);
            $templateModified = str_replace('{random_number}', generateRandomNumber(), $templateModified);

            // Tambahkan error_reporting di dalam file index.php yang digenerate untuk menonaktifkan Notice
            $templateModified = "<?php error_reporting(E_ALL & ~E_NOTICE); ?>\n" . $templateModified;

            // Simpan template yang sudah diubah menjadi index.php di folder brand
            $filePath = $cleanBrand . '/index.php';
            if (file_put_contents($filePath, $templateModified)) {
                echo "<script>console.log('File index.php berhasil dibuat di folder $cleanBrand');</script>";
            } else {
                echo "<script>alert('Gagal membuat file index.php di folder $cleanBrand');</script>";
            }
        }

        echo "<script>alert('Semua file index.php berhasil dibuat di masing-masing folder brand.');</script>";
    } else {
        echo "<script>alert('Jumlah baris di file yang diupload tidak konsisten. Pastikan semua file memiliki jumlah baris yang sama.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools Generator Landing Page</title>
    <style>
        /* Styling Dasar */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            transition: background-color 0.5s ease-in-out;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="file"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group input[type="submit"] {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, box-shadow 0.3s;
        }
        .form-group input[type="submit"]:hover {
            background: #218838;
            box-shadow: 0 0 10px #218838;
        }
        .instructions {
            background: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .toggle-mode {
            text-align: center;
            margin: 20px 0;
        }
        .toggle-mode button {
            padding: 10px 20px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .toggle-mode button:hover {
            background: #555;
            box-shadow: 0 0 10px #555;
        }
        /* Mode Gelap */
        body.dark-mode {
            background-color: #2c2c2c;
            color: #fff;
        }
        body.dark-mode .container {
            background: #424242;
        }
        body.dark-mode .form-group input[type="file"],
        body.dark-mode .form-group input[type="text"] {
            border: 1px solid #555;
            background: #555;
            color: #fff;
        }
        body.dark-mode .form-group input[type="submit"] {
            background: #007bff;
        }
        body.dark-mode .form-group input[type="submit"]:hover {
            background: #0056b3;
            box-shadow: 0 0 10px #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Tools Generator Landing Page</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="url">URL:</label>
            <input type="text" id="url" name="url" required>
        </div>
        <div class="form-group">
            <label for="link_daftar">Link Tujuan untuk Tombol Daftar:</label>
            <input type="text" id="link_daftar" name="link_daftar" required>
        </div>
        <div class="form-group">
            <label for="template">Upload Template (TXT):</label>
            <input type="file" id="template" name="template" accept=".txt" required>
        </div>
        <div class="form-group">
            <label for="brand">Upload Brand Name List (TXT):</label>
            <input type="file" id="brand" name="brand" accept=".txt" required>
        </div>
        <div class="form-group">
            <label for="meta_description">Upload Meta Description (TXT):</label>
            <input type="file" id="meta_description" name="meta_description" accept=".txt" required>
        </div>
        <div class="form-group">
            <label for="domain_description">Upload Domain Description (TXT):</label>
            <input type="file" id="domain_description" name="domain_description" accept=".txt" required>
        </div>
        <div class="form-group">
            <label for="title">Upload Title (TXT):</label>
            <input type="file" id="title" name="title" accept=".txt" required>
        </div>
        <div class="form-group">
            <label for="content_url">Upload Content URL (TXT):</label>
            <input type="file" id="content_url" name="content_url" accept=".txt" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Generate">
        </div>
    </form>
</div>
<div class="toggle-mode">
    <button onclick="toggleMode()">Toggle Dark/Light Mode</button>
</div>
<div class="instructions">
    <?php displayInstructions(); ?>
</div>
<script>
    function toggleMode() {
        document.body.classList.toggle('dark-mode');
    }
</script>
</body>
</html>
