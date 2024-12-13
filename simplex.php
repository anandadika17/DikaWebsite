<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perhitungan Simpleks</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php

// Fungsi untuk membuat tabel Simpleks dan memberikan penjelasan setelah tabel
function cetakTabelSimpleks($iterasi, $basis, $table, $variables, $rhs, $zj, $cj_zj, $pivot) {
    if ($iterasi == 0) {
        echo "<h2>Tabel Awal</h2>";
    } else {
        echo "<h2>Iterasi $iterasi</h2>";
    }

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Basis</th>";

    foreach ($variables as $var) {
        echo "<th>$var</th>";
    }
    echo "<th>RHS</th></tr>";

    for ($i = 0; $i < count($table); $i++) {
        echo "<tr>";
        echo "<td>{$basis[$i]}</td>";
        for ($j = 0; $j < count($table[$i]); $j++) {
            echo "<td>" . number_format($table[$i][$j], 2) . "</td>";
        }
        echo "<td>" . number_format($rhs[$i], 2) . "</td>";
        echo "</tr>";
    }

    echo "<tr><td>Zj</td>";
    foreach ($zj as $z) {
        echo "<td>" . number_format($z, 2) . "</td>";
    }
    echo "<td></td></tr>";

    echo "<tr><td>Cj - Zj</td>";
    foreach ($cj_zj as $cz) {
        echo "<td>" . number_format($cz, 2) . "</td>";
    }
    echo "<td></td></tr>";
    echo "</table>";

    // Penjelasan setelah tabel dengan penomoran
    if ($pivot) {
        echo "<p><strong>Penjelasan Iterasi $iterasi:</strong></p>";
        echo "<ol>"; // Menggunakan <ol> untuk penomoran otomatis
        echo "<li>Pilih Kolom Pivot: Kolom {$pivot['kolom']} dengan nilai Cj - Zj terbesar.</li>";
        echo "<li>Pilih Baris Pivot: Baris {$pivot['baris']} dengan rasio terkecil antara RHS dan elemen pivot.</li>";
        echo "<li>Normalisasi baris pivot sehingga elemen pivot menjadi 1.</li>";
        echo "<li>Eliminasi elemen-elemen lainnya pada kolom pivot untuk membuat kolom pivot menjadi 0.</li>";
        echo "<li>Perbarui basis, menggantikan variabel slack dengan variabel keputusan yang baru.</li>";
        echo "</ol>";
    }
}

// Fungsi utama untuk metode Simpleks
function metodeSimpleks($f_objektif, $constraints, $rhs, $operators, $n, $m) {
    $variables = array_map(fn($i) => "x$i", range(1, $n));
    $basis = array_map(fn($i) => "s$i", range(1, $m)); // Variabel slack

    // Membuat tabel awal Simpleks
    $table = $constraints;
    $zj = array_fill(0, $n, 0);
    $cj_zj = $f_objektif;

    // Tampilkan tabel awal
    cetakTabelSimpleks(0, $basis, $table, $variables, $rhs, $zj, $cj_zj, null);

    $iterasi = 1;
    while (max($cj_zj) > 0) { // Cj - Zj masih ada positif
        // Pilih kolom pivot (indeks dengan Cj-Zj terbesar)
        $pivotKolom = array_keys($cj_zj, max($cj_zj))[0];

        // Pilih baris pivot
        $rasio = [];
        foreach ($rhs as $i => $value) {
            if ($table[$i][$pivotKolom] > 0) {
                $rasio[$i] = $rhs[$i] / $table[$i][$pivotKolom];
            } else {
                $rasio[$i] = PHP_INT_MAX; // Tidak bisa menjadi pivot
            }
        }
        $pivotBaris = array_keys($rasio, min($rasio))[0];

        // Tentukan pivot
        $pivotElement = $table[$pivotBaris][$pivotKolom];
        $pivot = ["baris" => $pivotBaris + 1, "kolom" => $pivotKolom + 1];

        // Normalisasi baris pivot
        for ($j = 0; $j < count($table[$pivotBaris]); $j++) {
            $table[$pivotBaris][$j] /= $pivotElement;
        }
        $rhs[$pivotBaris] /= $pivotElement;

        // Eliminasi kolom pivot
        for ($i = 0; $i < count($table); $i++) {
            if ($i != $pivotBaris) {
                $factor = $table[$i][$pivotKolom];
                for ($j = 0; $j < count($table[$i]); $j++) {
                    $table[$i][$j] -= $factor * $table[$pivotBaris][$j];
                }
                $rhs[$i] -= $factor * $rhs[$pivotBaris];
            }
        }

        // Update Zj dan Cj - Zj
        for ($j = 0; $j < count($f_objektif); $j++) {
            $zj[$j] = 0;
            for ($i = 0; $i < count($basis); $i++) {
                $zj[$j] += $f_objektif[array_search($basis[$i], $variables)] * $table[$i][$j];
            }
        }
        for ($j = 0; $j < count($cj_zj); $j++) {
            $cj_zj[$j] = $f_objektif[$j] - $zj[$j];
        }

        // Perbarui basis
        $basis[$pivotBaris] = $variables[$pivotKolom];

        // Cetak tabel iterasi
        cetakTabelSimpleks($iterasi, $basis, $table, $variables, $rhs, $zj, $cj_zj, $pivot);

        $iterasi++;
    }

    // Solusi optimal
    echo "<h2>Solusi Optimal</h2>";
    echo "<p>Z = " . array_sum(array_map(fn($i) => $rhs[$i] * $f_objektif[array_search($basis[$i], $variables)], range(0, count($basis) - 1))) . "</p>";
    echo "<p>Variabel Keputusan:</p><ul>";
    foreach ($variables as $var) {
        $index = array_search($var, $basis);
        $value = $index === false ? 0 : $rhs[$index];
        echo "<li>$var = " . number_format($value, 2) . "</li>";
    }
    echo "</ul>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = intval($_POST['n']);
    $m = intval($_POST['m']);
    $f_objektif = $_POST['f_objektif'];
    $constraints = $_POST['constraints'];
    $rhs = $_POST['rhs'];
    $operators = $_POST['operators'];

    metodeSimpleks($f_objektif, $constraints, $rhs, $operators, $n, $m);
}
?>

<div class="button-container">
    <a href="form_input.php" class="button">Kembali ke Formulir</a>
	
</div>

</body>
</html>
