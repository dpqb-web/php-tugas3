<?php $conn = new PDO("mysql:host=localhost;dbname=tgs3", "tes1", "tes123");
session_start();

include_once "../mod/Parsedown.php";
$Parsedown = new Parsedown();

// $ambil = "SELECT * FROM :tabel :tambahan"; // [WHERE] [ORDER BY [DESC]] [LIMIT [OFFSET]]
// $tambah = "INSERT INTO :tabel VALUES (DEFAULT, :isi)";
// $ubah = "UPDATE :tabel SET :isi WHERE :ciri";
// $hapus = "DELETE FROM :tabel WHERE :ciri";

function kue($nama, $isi) {
  setcookie($nama, $isi, time() + 1);
}
function sql_isikan($sql, $kunci2) {
  foreach ($kunci2 as $x) { $sql -> bindValue($x[1], $x[2], $x[0]); }
}
function sql_tambahkan($kunci2) {
  foreach ($kunci2 as $x) { $kunci1[] = $x[1]; }
  $hasil1 = "(" . implode(", ", $kunci1) . ")";
  $hasil2 = "(:" . implode(", :", $kunci1) . ")";

  return [$hasil1, $hasil2];
}
function sql_suntingkan($kunci2) {
  foreach ($kunci2 as $x) { $kunci1[] = $x[1] . " = :" . $x[1]; }
  return implode(", ", $kunci1);
}
 ?><?php if (empty($_SESSION["id"])) header("Location: /");

if (isset($_POST["tambah"])) {
  $nama_jadwal = htmlspecialchars($_POST["tambah"]);

  if (!empty($_POST["pembuat"])) {
    $pembuat = htmlspecialchars($_POST["pembuat"]);
    if (isset($kunci2)) $kunci2[] = [PDO::PARAM_INT, "pembuat", $pembuat];
  } else {
    if ($nama_jadwal == "karya") {
      $judul = htmlspecialchars($_POST["judul"]);
      $keterangan = htmlspecialchars($_POST["keterangan"]);
      $tautan = htmlspecialchars($_POST["tautan"]);

      if (!empty($judul)) {
        $kunci2 = [
          [PDO::PARAM_STR, "judul", $judul],
          [PDO::PARAM_STR, "keterangan", $keterangan],
          [PDO::PARAM_STR, "tautan", $tautan],
        ];
      } else {
        kue("galat", "isian-kosong");
      }
    }
    if ($nama_jadwal == "catatan") {
      $judul = htmlspecialchars($_POST["judul"]);
      $isi = htmlspecialchars($_POST["isi"]);

      if (!empty($judul) && !empty($isi)) {
        $kunci2 = [
          [PDO::PARAM_STR, "judul", $judul],
          [PDO::PARAM_STR, "isi", $isi],
        ];
      } else {
        kue("galat", "isian-kosong");
      }
    }
  }

  if (isset($kunci2)) {
    $isian = sql_tambahkan($kunci2);
    $perintah = $conn -> prepare("INSERT INTO $nama_jadwal $isian[0] VALUES $isian[1]");

    sql_isikan($perintah, $kunci2);
    $perintah -> execute();
  }

  header("Location: /admin.php?" . $nama_jadwal);
}

if (isset($_POST["sunting"])) {
  $nama_jadwal = htmlspecialchars($_POST["sunting"]);
  $id = htmlspecialchars($_POST["id"]);

  if (!empty($id)) {
    $petunjuk2 = [[PDO::PARAM_INT, "id", $id]];

    if ($nama_jadwal == "karya") {
      $judul = htmlspecialchars($_POST["judul"]);
      $keterangan = htmlspecialchars($_POST["keterangan"]);
      $tautan = htmlspecialchars($_POST["tautan"]);

      if (!empty($judul)) {
        $kunci2 = [
          [PDO::PARAM_INT, "pembuat", $pembuat],
          [PDO::PARAM_STR, "judul", $judul],
          [PDO::PARAM_STR, "keterangan", $keterangan],
          [PDO::PARAM_STR, "tautan", $tautan],
        ];
      } else {
        kue("galat", "isian-kosong");
      }
    }
    if ($nama_jadwal == "catatan") {
      $judul = htmlspecialchars($_POST["judul"]);
      $isi = htmlspecialchars($_POST["isi"]);

      if (!empty($judul) && !empty($isi)) {
        $kunci2 = [
          [PDO::PARAM_STR, "judul", $judul],
          [PDO::PARAM_STR, "isi", $isi],
        ];
      } else {
        kue("galat", "isian-kosong");
      }
    }
  } else {
    if ($nama_jadwal == "data_diri") {
      $nama_lengkap = htmlspecialchars($_POST["nama_lengkap"]);
      $keterangan = htmlspecialchars($_POST["keterangan"]);

      if (!empty($nama_lengkap) && !empty($keterangan)) {
        $kunci2 = [
          [PDO::PARAM_STR, "nama_lengkap", $nama_lengkap],
          [PDO::PARAM_STR, "keterangan", $keterangan],
        ];
      } else {
        kue("galat", "isian-kosong");
      }
    }
    if ($nama_jadwal == "pengguna") {
      $lengkap = htmlspecialchars($_POST["lengkap"]);
      $nama = htmlspecialchars($_POST["nama"]);

      if (!empty($lengkap) && !empty($nama)) {
        $kunci2 = [
          [PDO::PARAM_STR, "lengkap", $lengkap],
          [PDO::PARAM_STR, "nama", $nama],
        ];
        $petunjuk2 = [[PDO::PARAM_INT, "id", $_SESSION["id"]]];
      } else {
        kue("galat", "isian-kosong");
      }
    }
  }

  if (isset($kunci2)) {
    $isian = sql_suntingkan($kunci2);
    $siap = "UPDATE $nama_jadwal SET $isian";
    if (!empty($id)) $siap += " WHERE id = :id";
    $perintah = $conn -> prepare($siap);

    sql_isikan($perintah, $kunci2);
    if (!empty($petunjuk2)) sql_isikan($perintah, $petunjuk2);
    $perintah -> execute();
  }

  if ($nama_jadwal == "pengguna") {
    header("Location: /?pengguna=keluar");
  } else {
    header("Location: /admin.php?" . $nama_jadwal);
  }
}

if (isset($_POST["hapus"])) {
  $nama_jadwal = htmlspecialchars($_POST["hapus"]);

  if ($nama_jadwal == "pengguna") {
    $petunjuk2 = [[PDO::PARAM_INT, "id", $_SESSION["id"]]];
  } else {
    $id = htmlspecialchars($_POST["id"]);

    if (!empty($id)) {
      $petunjuk2 = [[PDO::PARAM_INT, "id", $id]];
    } else {
      kue("galat", "id-kosong");
    }
  }

  if (isset($petunjuk2)) {
    var_dump($petunjuk2);
    $perintah = $conn -> prepare("DELETE FROM $nama_jadwal WHERE id = :id");

    sql_isikan($perintah, $petunjuk2);
    $perintah -> execute();
  }

  if ($nama_jadwal == "pengguna") {
    header("Location: /?pengguna=keluar");
  } else {
    header("Location: /admin.php?" . $nama_jadwal);
  }
}
 ?><!DOCTYPE html>
<html lang="id" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
    <meta name="X-UA-Compatible" content="IE=edge">
    <meta name="generator" content="node, coffee, pug, sass, marked">
    <meta name="author" content="Muhammad Rizki Fauzan">
    <title>Admin | Rizki Fauzan</title>
    <style>* {
  margin: 0;
  padding: 0;
  border: 0;
  outline: 0;
  font-size: 100%;
  background: rgba(0, 0, 0, 0);
  box-sizing: border-box;
}

input, button, textarea {
  background: inherit;
  font: inherit;
  color: inherit;
}

[hidden], .hidden {
  display: none !important;
}

/*  #000
 *  #1A1A1A
 *  #333
 *  #7F7F7F
 *  #CCC
 *  #E6E6E6
 *  #FFF
 *  */
html {
  font-family: InterDisplay, sans-serif;
  font-size: 13px;
  font-weight: 400;
  font-feature-settings: "liga", "calt", "ss02";
  color: #1A1A1A;
  background-color: #E6E6E6;
  background-image: url("https://www.transparenttextures.com/patterns/cardboard.png");
  background-position: top center;
  background-repeat: repeat;
  line-height: 1.5;
  text-align: justify;
}

a, :link, :visited, :any-link {
  color: inherit;
  cursor: pointer;
  text-decoration-thickness: 1px;
  text-decoration-style: dotted;
  text-decoration-color: rgba(26, 26, 26, 0.4980392157);
  text-decoration-line: underline;
}
a:hover, :link:hover, :visited:hover, :any-link:hover {
  text-decoration-style: solid;
  text-decoration-color: #1A1A1A;
}

body {
  max-inline-size: 960px;
  margin: auto;
  padding-block: 10px;
  padding-inline: 0;
}

button {
  padding-block: 0;
  padding-inline: 15px;
  background: linear-gradient(to bottom, #E6E6E6, rgb(223.5, 223.5, 223.5) 50%, rgb(210.5, 210.5, 210.5) 51%, #CCC);
  box-shadow: inset 0 -1px 5px rgba(255, 255, 255, 0.4980392157);
  border: 1px solid #333;
  border-radius: 3px;
  transition: box-shadow 0.1s ease-in-out;
  min-inline-size: 80px;
  text-align: center;
}
button:focus {
  box-shadow: inset 0 -1px 5px rgba(26, 127, 230, 0.4980392157), 0 0 3px #1A7FE6;
  border-color: #1A7FE6;
}
button:focus:hover {
  box-shadow: inset 0 -1px 5px rgba(26, 127, 230, 0.9019607843), 0 0 3px #1A7FE6;
}
button:hover {
  box-shadow: inset 0 -1px 5px rgba(255, 255, 255, 0.9019607843);
}
button:active, button:focus:active {
  box-shadow: inset 0 1px 5px #33C;
  border-color: #333;
  background: linear-gradient(to top, #E6E6E6, rgb(223.5, 223.5, 223.5) 50%, rgb(210.5, 210.5, 210.5) 51%, #CCC);
}
button:disabled {
  background: linear-gradient(to bottom, #CCC, rgb(184.75, 184.75, 184.75) 50%, rgb(146.25, 146.25, 146.25) 51%, #7F7F7F);
}

label .kotak-isian {
  padding-block: 3px;
  padding-inline: 5px;
  background: #fff;
  box-shadow: inset 0 0 5px rgba(127, 127, 127, 0.4980392157);
  border: 1px solid #333;
  border-radius: 3px;
  cursor: text;
  transition: box-shadow 0.1s ease-in-out;
}
label .kotak-isian:hover {
  box-shadow: inset 0 0 5px rgba(127, 127, 127, 0.8);
}
label .kotak-isian:has(:focus) {
  box-shadow: inset 0 0 5px rgba(26, 127, 230, 0.4980392157), 0 0 3px #1A7FE6;
  border-color: #1A7FE6;
}
label .kotak-isian:has(:focus):hover {
  box-shadow: inset 0 0 5px rgba(26, 127, 230, 0.8), 0 0 3px #1A7FE6;
}
label .kotak-isian:has(:disabled) {
  color: #7F7F7F;
  background: #E6E6E6;
  cursor: default;
}
label .kotak-isian * {
  width: 100%;
  height: 100%;
}
label .kotak-isian textarea {
  min-inline-size: 438px;
}

ul, ol {
  padding-inline-start: 1.3em;
}

p {
  margin-block: 1rem;
}

code, pre {
  font-family: Iosevka, monospace;
  font-feature-settings: "calt";
}

h6 {
  font-size: 15px;
  font-weight: 600;
  margin-block: 1rem;
  margin-inline: 0;
  line-height: 1.3;
}

h5 {
  font-size: 17px;
  font-weight: 600;
  margin-block: 1rem;
  margin-inline: 0;
  line-height: 1.3;
}

h4 {
  font-size: 19px;
  font-weight: 600;
  margin-block: 1rem;
  margin-inline: 0;
  line-height: 1.3;
}

h3 {
  font-size: 21px;
  font-weight: 600;
  margin-block: 1rem;
  margin-inline: 0;
  line-height: 1.3;
}

h2 {
  font-size: 23px;
  font-weight: 600;
  margin-block: 1rem;
  margin-inline: 0;
  line-height: 1.3;
}

h1 {
  font-size: 25px;
  font-weight: 600;
  margin-block: 1rem;
  margin-inline: 0;
  line-height: 1.3;
}

::selection {
  background: #1A1A1A;
  color: #E6E6E6;
  text-shadow: none;
}

.permintaan {
  position: fixed;
  top: 0;
  left: 0;
  inline-size: 100%;
  block-size: 100%;
  backdrop-filter: grayscale(0.5) brightness(0.5);
  display: flex;
}
.permintaan .kotak {
  margin: auto;
  background: #E6E6E6;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 5px 20px 20px rgba(51, 51, 51, 0.2);
  min-inline-size: 480px;
  min-block-size: 240px;
  display: flex;
  flex-flow: column nowrap;
}
.permintaan .kotak .kepala > :first-child, .permintaan .kotak .isi > :first-child {
  margin-block-start: 0;
}
.permintaan .kotak .kepala > :last-child, .permintaan .kotak .isi > :last-child {
  margin-block-end: 0;
}
.permintaan .kotak .kepala {
  margin-block-end: 1rem;
}
.permintaan .kotak .akhir {
  display: flex;
  flex-flow: row-reverse nowrap;
  margin-block-start: auto;
  padding-block-start: 10px;
}
.permintaan .kotak .akhir .aman {
  margin-inline-start: auto;
}

.utama {
  margin-block: 20px;
  margin-inline: 0;
}
.utama.kepala {
  display: flex;
  justify-content: space-between;
  border-radius: 5px;
  color: #E6E6E6;
  margin-block-start: 0;
  background: linear-gradient(to top, #1A1A1A, rgb(32.25, 32.25, 32.25) 50%, rgb(44.75, 44.75, 44.75) 51%, #333);
  box-shadow: 0 1px 3px rgba(51, 51, 51, 0.2);
}
.utama.kepala a, .utama.kepala :link, .utama.kepala :visited, .utama.kepala :any-link {
  min-inline-size: 80px;
  padding: 10px;
  display: inline-block;
  text-align: center;
  text-decoration: none !important;
}
.utama.kepala button {
  background: none;
  border: none;
  box-shadow: none;
}
.utama.isi {
  background-color: #FFF;
  border-radius: 5px;
  box-shadow: 0 1px 3px rgba(51, 51, 51, 0.2);
  padding: 10px;
}
.utama.isi .kepala:before, .utama.isi .kepala:after, .utama.isi .isi:before, .utama.isi .isi:after {
  content: "";
  display: table;
  clear: both;
}
.utama.isi .kepala > :first-child, .utama.isi .isi > :first-child {
  margin-block-start: 0;
}
.utama.isi .kepala > :last-child, .utama.isi .isi > :last-child {
  margin-block-end: 0;
}
.utama.isi .kepala {
  margin-block-end: 10px;
}
.utama.isi .kepala h6 {
  display: inline-block;
  margin-block: 0;
}
.utama.isi .kepala h5 {
  display: inline-block;
  margin-block: 0;
}
.utama.isi .kepala h4 {
  display: inline-block;
  margin-block: 0;
}
.utama.isi .kepala h3 {
  display: inline-block;
  margin-block: 0;
}
.utama.isi .kepala h2 {
  display: inline-block;
  margin-block: 0;
}
.utama.isi .kepala h1 {
  display: inline-block;
  margin-block: 0;
}
.utama.isi .kepala .tombol2 {
  float: inline-end;
}
.utama.isi table {
  inline-size: 100%;
}
.utama.isi hr {
  border-block-start: 1px dashed rgba(0, 0, 0, 0.4980392157);
  margin-block: 1em;
  margin-inline: 0;
}
.utama.isi :not(pre) > code {
  padding-block: 3px;
  padding-inline: 5px;
  border-radius: 3px;
  background-color: #E6E6E6;
  display: inline-block;
}
.utama.isi pre {
  padding-block: 3px;
  padding-inline: 5px;
  border-radius: 3px;
  background-color: #E6E6E6;
  margin-block: 3px;
}
.utama.isi .petak {
  display: flex;
  flex-flow: row wrap;
  gap: 20px;
}
.utama.isi .petak .buah {
  inline-size: 460px;
}
.utama.isi .petak .buah .tautan {
  float: right;
}
.utama.isi .petak .buah:not(:hover) .tautan a, .utama.isi .petak .buah:not(:hover) .tautan :link, .utama.isi .petak .buah:not(:hover) .tautan :visited, .utama.isi .petak .buah:not(:hover) .tautan :any-link {
  opacity: 0;
}
.utama.isi.catatan.tulisan h6 {
  font-weight: 200;
}
.utama.isi.catatan.tulisan h5 {
  font-weight: 200;
}
.utama.isi.catatan.tulisan h4 {
  font-weight: 200;
}
.utama.isi.catatan.tulisan h3 {
  font-weight: 200;
}
.utama.isi.catatan.tulisan h2 {
  font-weight: 200;
}
.utama.isi.catatan.tulisan h1 {
  font-weight: 200;
}
.utama.isi.admin.halaman {
  display: flex;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0) 49%, #7F7F7F 50%, #FFF 51%, #FFF), linear-gradient(to bottom, #E6E6E6, #CCC);
}
.utama.isi.admin.halaman .pilihan {
  margin: auto;
  border: 1px solid #7F7F7F;
  border-radius: 3px;
  overflow: hidden;
}
.utama.isi.admin.halaman .pilihan a, .utama.isi.admin.halaman .pilihan :link, .utama.isi.admin.halaman .pilihan :visited, .utama.isi.admin.halaman .pilihan :any-link {
  padding-block: 0;
  padding-inline: 15px;
  background: linear-gradient(to bottom, #E6E6E6, #CCC);
  box-shadow: inset 0 -1px 5px rgba(255, 255, 255, 0.4980392157);
  border-inline-end: 1px solid #CCC;
  transition: box-shadow 0.1s ease-in-out;
  min-inline-size: 80px;
  text-align: center;
  display: inline-block;
  text-decoration: none;
}
.utama.isi.admin.halaman .pilihan a:last-child, .utama.isi.admin.halaman .pilihan :link:last-child, .utama.isi.admin.halaman .pilihan :visited:last-child, .utama.isi.admin.halaman .pilihan :any-link:last-child {
  border-inline-end: 0;
}
.utama.isi.admin.halaman .pilihan a:hover, .utama.isi.admin.halaman .pilihan :link:hover, .utama.isi.admin.halaman .pilihan :visited:hover, .utama.isi.admin.halaman .pilihan :any-link:hover {
  box-shadow: inset 0 -1px 5px rgba(255, 255, 255, 0.9019607843);
}
.utama.isi.admin.halaman .pilihan a:active, .utama.isi.admin.halaman .pilihan a:focus:active, .utama.isi.admin.halaman .pilihan :link:active, .utama.isi.admin.halaman .pilihan :link:focus:active, .utama.isi.admin.halaman .pilihan :visited:active, .utama.isi.admin.halaman .pilihan :visited:focus:active, .utama.isi.admin.halaman .pilihan :any-link:active, .utama.isi.admin.halaman .pilihan :any-link:focus:active {
  box-shadow: inset 0 1px 5px #33C;
  border-color: #7F7F7F;
  background: linear-gradient(to top, #E6E6E6, #CCC);
}
.utama.kaki {
  text-align: center;
  text-shadow: 0 1px #E6E6E6;
  color: #7F7F7F;
  margin-block-end: 0;
}

.tes-petak {
  position: fixed;
  inset: 0;
  background: white;
  display: flex;
  opacity: 0.1;
}
.tes-petak div {
  inline-size: 960px;
  block-size: 100%;
  background: blue;
  display: flex;
  margin: auto;
}
.tes-petak div div {
  inline-size: 60px;
  margin: 0 10px;
  display: inline-block;
  background: yellow;
}
    </style>
    <style>
      @import "https://rsms.me/inter/inter.css";
      
      /* iosevka-latin-100-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 100;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-100-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-100-normal.woff) format('woff');
      }
      
      /* iosevka-latin-200-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 200;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-200-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-200-normal.woff) format('woff');
      }
      
      /* iosevka-latin-300-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 300;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-300-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-300-normal.woff) format('woff');
      }
      
      /* iosevka-latin-400-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 400;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-400-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-400-normal.woff) format('woff');
      }
      
      /* iosevka-latin-500-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 500;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-500-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-500-normal.woff) format('woff');
      }
      
      /* iosevka-latin-600-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 600;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-600-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-600-normal.woff) format('woff');
      }
      
      /* iosevka-latin-700-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 700;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-700-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-700-normal.woff) format('woff');
      }
      
      /* iosevka-latin-800-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 800;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-800-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-800-normal.woff) format('woff');
      }
      
      /* iosevka-latin-900-normal */
      @font-face {
        font-family: 'Iosevka';
        font-style: normal;
        font-display: auto;
        font-weight: 900;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-900-normal.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-900-normal.woff) format('woff');
      }
      
      /* iosevka-latin-100-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 100;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-100-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-100-italic.woff) format('woff');
      }
      
      /* iosevka-latin-200-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 200;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-200-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-200-italic.woff) format('woff');
      }
      
      /* iosevka-latin-300-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 300;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-300-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-300-italic.woff) format('woff');
      }
      
      /* iosevka-latin-400-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 400;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-400-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-400-italic.woff) format('woff');
      }
      
      /* iosevka-latin-500-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 500;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-500-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-500-italic.woff) format('woff');
      }
      
      /* iosevka-latin-600-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 600;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-600-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-600-italic.woff) format('woff');
      }
      
      /* iosevka-latin-700-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 700;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-700-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-700-italic.woff) format('woff');
      }
      
      /* iosevka-latin-800-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 800;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-800-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-800-italic.woff) format('woff');
      }
      
      /* iosevka-latin-900-italic */
      @font-face {
        font-family: 'Iosevka';
        font-style: italic;
        font-display: auto;
        font-weight: 900;
        src: url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-900-italic.woff2) format('woff2'), url(https://cdn.jsdelivr.net/fontsource/fonts/iosevka@latest/latin-900-italic.woff) format('woff');
      }
    </style>
  </head>
  <body>
    <div class="kepala utama">
      <div class="kiri"><a href="/">Beranda</a><a href="/?karya">Karya</a><a href="/?catatan">Catatan</a>
        <!--
        form(action="/")
          label: input(name="cari", placeholder="Cari", required, type="search")
          button(type="submit") Cari
        -->
      </div>
      <div class="kanan"><?php if (isset($_SESSION["id"])): ?><a href="/admin.php"><?= $_SESSION["lengkap"] ?></a><a data-kemana="/?pengguna=keluar">Keluar</a><?php else: ?><a data-kemana="/?pengguna=masuk">Masuk</a><a data-kemana="/?pengguna=daftar">Daftar</a><?php endif; ?></div>
    </div>
    <div class="isi utama admin halaman">
      <div class="pilihan"><a href="/admin.php">Pengguna</a><?php if ($_SESSION["jenis"] == "admin"): ?><a href="/admin.php?data_diri">Data Diri</a><a href="/admin.php?karya">Karya</a><a href="/admin.php?catatan">Catatan</a><?php endif; ?><a href="/admin.php?komentar">Komentar</a></div>
    </div><?php if (isset($_GET['data_diri'])): ?>
    <div class="isi utama"><?php $hasil = $conn -> query("SELECT * FROM data_diri") -> fetch(); ?>
      <div class="kepala">
        <h1><?= $hasil["nama_lengkap"] ?></h1>
        <div class="tombol2">
          <button data-kemana="/admin.php?data_diri=sunting">Sunting</button>
        </div>
      </div>
      <div class="isi"><?= $Parsedown -> text($hasil["keterangan"]) ?></div>
    </div><?php elseif (isset($_GET['karya'])): ?>
    <div class="isi utama">
      <div class="kepala">
        <div class="tombol2">
          <button data-kemana="/admin.php?karya=tambah">Tambah</button>
        </div>
      </div><?php $hasil2 = $conn -> query("SELECT * FROM karya ORDER BY id DESC") -> fetchAll(); ?>
      <div class="isi"><?php if ($hasil2): ?>
        <div class="petak"><?php foreach ($hasil2 as $hasil): ?>
          <div class="buah">
            <div class="kepala">
              <h4><a href="<?= $hasil["tautan"] ?>"><?= $hasil["judul"] ?></a></h4>
            </div>
            <div class="isi"><span><?= $hasil["keterangan"] ?></span>
              <div class="tautan"><a data-kemana="/admin.php?karya=sunting&id=<?= $hasil['id'] ?>">Sunting</a>
                <a data-kemana="/admin.php?karya=hapus&id=<?= $hasil['id'] ?>">Hapus</a>
              </div>
            </div>
          </div><?php endforeach; ?>
        </div><?php else: ?>
        <center>Tidak ada karya</center><?php endif; ?>
      </div>
    </div><?php elseif (isset($_GET['catatan'])): ?>
    <div class="isi utama">
      <div class="kepala">
        <div class="tombol2">
          <button data-kemana="/admin.php?catatan=tambah">Tambah</button>
        </div>
      </div><?php $hasil2 = $conn -> query("SELECT * FROM catatan ORDER BY id DESC") -> fetchAll(); ?>
      <div class="isi"><?php if ($hasil2): ?>
        <div class="petak"><?php foreach ($hasil2 as $hasil): ?>
          <div class="buah">
            <div class="kepala">
              <h4><?= $hasil["judul"] ?></h4>
            </div>
            <div class="isi"><span><i>
                  dibuat pada
                  <?= $hasil["waktu_dibuat"] ?></i></span>
              <div class="tautan"><a data-kemana="/admin.php?catatan=sunting&id=<?= $hasil['id'] ?>">Sunting</a>
                <a data-kemana="/admin.php?catatan=hapus&id=<?= $hasil['id'] ?>">Hapus</a>
              </div>
            </div>
          </div><?php endforeach; ?>
        </div><?php else: ?>
        <center>Tidak ada catatan</center><?php endif; ?>
      </div>
    </div><?php elseif (isset($_GET['komentar'])): ?>
    <div class="isi utama"><?php if ($_SESSION["jenis"] == "admin") {
  $hasil2 = $conn -> query("SELECT * FROM komentar ORDER BY id DESC") -> fetchAll();
} else {
  $perintah = $conn -> prepare("SELECT * FROM komentar WHERE pembuat = ? ORDER BY id DESC");
  $perintah -> execute([$_SESSION["id"]]);
  $hasil2 = $perintah -> fetchAll();
} ?>
      <div class="isi"><?php if ($hasil2): ?>
        <div class="petak"><?php foreach ($hasil2 as $hasil): ?>
          <div class="buah">
            <div class="isi">
              <p><?= $hasil["isi"] ?></p><?php if ($_SESSION["jenis"] == "admin"): ?>
              <p><i>
                  oleh
                  <?= $hasil["pembuat"] ?></i></p><?php endif; ?><span><i>
                  dalam
                  <a href="/?komentar=<?= $hasil["komentar"] ?>"><?= $hasil["komentar"] ?></a></i></span>
              <div class="tautan"><a data-kemana="/admin.php?catatan=sunting&id=<?= $hasil['id'] ?>">Sunting</a>
                <a data-kemana="/admin.php?catatan=hapus&id=<?= $hasil['id'] ?>">Hapus</a>
              </div>
            </div>
          </div><?php endforeach; ?>
        </div><?php else: ?>
        <center><i>Segera...</i></center><?php endif; ?>
      </div>
    </div><?php else: ?>
    <div class="isi utama">
      <div class="kepala">
        <h1><?= $_SESSION["lengkap"] ?></h1>
        <div class="tombol2">
          <button data-kemana="/admin.php?pengguna=hapus">Hapus</button>
          <button data-kemana="/admin.php?pengguna=sunting">Sunting</button>
        </div>
      </div>
      <div class="isi">
        <ul>
          <li><b>Nama pengguna:</b>
            <?= $_SESSION["nama"] ?>
          </li>
          <li><b>Waktu Dibuat:</b>
            <?= $_SESSION["waktu_dibuat"] ?>
          </li>
        </ul>
      </div>
    </div><?php endif; ?>
    <div class="kaki utama"><span>2025, Muhammad Rizki Fauzan</span></div><?php if (!empty($_GET["catatan"])): ?><?php $nama_jadwal = "catatan";
$tindakan = htmlspecialchars($_GET["catatan"]); ?><?php if ($tindakan == "tambah"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Tambah Catatan</h3>
        </div>
        <div class="isi">
          <form id="data" action="/admin.php" method="post">
            <label><span>Judul</span>
              <div class="kotak-isian">
                <input name="judul" autofocus style="font-size: 19px">
              </div>
            </label>
            <label><span>Isi</span>
              <div class="kotak-isian">
                <textarea name="isi"></textarea>
              </div>
            </label>
          </form>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Tambah</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php if (isset($_GET["id"])): ?><?php $id = htmlspecialchars($_GET["id"]) ?><?php $perintah = $conn -> prepare("SELECT * FROM $nama_jadwal WHERE id = ?");
$perintah -> execute([$id]);
$hasil = $perintah -> fetch(); ?><?php if ($tindakan == "sunting"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Sunting Catatan</h3>
        </div>
        <div class="isi">
          <form id="data" action="/admin.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label><span>Judul</span>
              <div class="kotak-isian">
                <input name="judul" autofocus value="<?= $hasil["judul"] ?>" style="font-size: 19px">
              </div>
            </label>
            <label><span>Isi</span>
              <div class="kotak-isian">
                <textarea name="isi"><?= $hasil["isi"] ?></textarea>
              </div>
            </label>
          </form>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Sunting</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php if ($tindakan == "hapus"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Hapus Catatan</h3>
        </div>
        <div class="isi">
          <p>Apakah anda yakin ingin menghapus catatan berikut?</p>
          <p>Tindakan ini tidak dapat diurungkan!</p>
          <form id="data" action="/admin.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">
          </form>
          <h6><?= $hasil["judul"] ?></h6>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
          </div>
          <div class="bahaya">
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Hapus</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php endif; ?><?php endif; ?><?php if (!empty($_GET["karya"])): ?><?php $nama_jadwal = "karya";
$tindakan = htmlspecialchars($_GET["karya"]); ?><?php if ($tindakan == "tambah"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Tambah Karya</h3>
        </div>
        <div class="isi">
          <form id="data" action="/admin.php" method="post">
            <label><span>Judul</span>
              <div class="kotak-isian">
                <input name="judul" autofocus style="font-size: 19px">
              </div>
            </label>
            <label><span>Tautan</span>
              <div class="kotak-isian">
                <input type="url" name="tautan">
              </div>
            </label>
            <label><span>Keterangan</span>
              <div class="kotak-isian">
                <textarea name="keterangan"></textarea>
              </div>
            </label>
          </form>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Tambah</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php if (isset($_GET["id"])): ?><?php $id = htmlspecialchars($_GET["id"]) ?><?php $perintah = $conn -> prepare("SELECT * FROM $nama_jadwal WHERE id = ?");
$perintah -> execute([$id]);
$hasil = $perintah -> fetch(); ?><?php if ($tindakan == "sunting"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Sunting Karya</h3>
        </div>
        <div class="isi">
          <form id="data" action="/admin.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label><span>Judul</span>
              <div class="kotak-isian">
                <input name="judul" autofocus value="<?= $hasil["judul"] ?>" style="font-size: 19px">
              </div>
            </label>
            <label><span>Tautan</span>
              <div class="kotak-isian">
                <input type="url" name="tautan" value="<?= $hasil["tautan"] ?>">
              </div>
            </label>
            <label><span>Keterangan</span>
              <div class="kotak-isian">
                <textarea name="keterangan"><?= $hasil["keterangan"] ?></textarea>
              </div>
            </label>
          </form>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Sunting</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php if ($tindakan == "hapus"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Hapus Karya</h3>
        </div>
        <div class="isi">
          <p>Apakah anda yakin ingin menghapus karya berikut?</p>
          <p>Tindakan ini tidak dapat diurungkan!</p>
          <form id="data" action="/admin.php" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">
          </form>
          <h6><?= $hasil["judul"] ?></h6>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
          </div>
          <div class="bahaya">
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Hapus</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php endif; ?><?php endif; ?><?php if (!empty($_GET["pengguna"])): ?><?php $nama_jadwal = "pengguna";
$tindakan = htmlspecialchars($_GET["pengguna"]); ?><?php if ($tindakan == "sunting"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Sunting Pengguna</h3>
        </div>
        <div class="isi">
          <form id="data" action="/admin.php" method="post">
            <label><span>Nama lengkap</span>
              <div class="kotak-isian">
                <input name="lengkap" autofocus value="<?= $_SESSION["lengkap"] ?>" style="font-size: 19px">
              </div>
            </label>
            <label><span>Nama pengguna</span>
              <div class="kotak-isian">
                <input name="nama" value="<?= $_SESSION["nama"] ?>">
              </div>
            </label>
            <label><span>Kata sandi</span>
              <div class="kotak-isian">
                <input value="Segera..." disabled>
              </div>
            </label>
          </form>
          <p>Mengganti isi dari bagian pengguna memerlukan pengguna untuk masuk ulang.</p>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Sunting</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php if ($tindakan == "hapus"): ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Hapus Pengguna</h3>
        </div>
        <div class="isi"><?php if ($_SESSION["jenis"] == "admin") $jenis = "Administrator";
else $jenis = "User"; ?>
          <p>Apakah anda yakin ingin menghapus pengguna ini?</p>
          <p>Tindakan ini tidak dapat diurungkan!</p>
          <form id="data" action="/admin.php" method="post"></form>
          <h4><?= $_SESSION["lengkap"] ?></h4>
          <p><b>Status:</b>
            <?= $jenis ?>
          </p><?php if($_SESSION["jenis"] == "admin"): ?>
          <p>Apabila tidak ada akun bergelar "Administrator" yang tersisa maka data-data dalam blog situs ini tidak akan dapat diubah!</p><?php endif; ?>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
          </div>
          <div class="bahaya">
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Hapus</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php endif; ?><?php if (!empty($_GET["data_diri"])): ?><?php $nama_jadwal = "data_diri";
$tindakan = htmlspecialchars($_GET["data_diri"]); ?><?php if ($tindakan == "sunting"): ?><?php $hasil = $conn -> query("SELECT * FROM data_diri") -> fetch(); ?>
    <div class="permintaan">
      <div class="kotak">
        <div class="kepala">
          <h3>Sunting Data Diri</h3>
        </div>
        <div class="isi">
          <form id="data" action="/admin.php" method="post">
            <label><span>Nama lengkap</span>
              <div class="kotak-isian">
                <input name="nama_lengkap" autofocus value="<?= $hasil["nama_lengkap"] ?>" style="font-size: 19px">
              </div>
            </label>
            <label><span>Keterangan</span>
              <div class="kotak-isian">
                <textarea name="keterangan"><?= $hasil["keterangan"] ?></textarea>
              </div>
            </label>
          </form>
        </div>
        <div class="akhir">
          <div class="aman">
            <button data-kemana="/admin.php?<?= $nama_jadwal ?>">Batal</button>
            <button form="data" type="submit" name="<?= $tindakan ?>" value="<?= $nama_jadwal ?>">Sunting</button>
          </div>
        </div>
      </div>
    </div><?php endif; ?><?php endif; ?>
    <script>(function() {
  var buah, i, len, ref;

  ref = document.querySelectorAll("button.batal");
  for (i = 0, len = ref.length; i < len; i++) {
    buah = ref[i];
    buah.addEventListener("click", function(ev) {
      return window.location.replace("/admin.php");
    });
  }

}).call(this);

    </script>
    <script>(function() {
  document.querySelectorAll("[data-kemana]").forEach(function(buah) {
    return buah.addEventListener("click", function(ev) {
      window.location.replace(buah.dataset.kemana);
      if (ev.preventDefault) {
        return ev.preventDefault();
      } else {
        return ev.returnValue = false;
      }
    });
  });

}).call(this);

    </script>
    <div class="tes-petak" hidden>
      <div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </body>
</html>