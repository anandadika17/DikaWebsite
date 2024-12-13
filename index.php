<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metode Simpleks</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function buatForm() {
            const n = document.getElementById("n").value;
            const m = document.getElementById("m").value;

            if (n > 0 && m > 0) {
                // Bagian Fungsi Objektif
                const fungsiObjektifDiv = document.getElementById("fungsiObjektif");
                fungsiObjektifDiv.innerHTML = "<h3>Fungsi Objektif</h3><p>Masukkan koefisien untuk setiap variabel keputusan:</p>";
                for (let i = 1; i <= n; i++) {
                    fungsiObjektifDiv.innerHTML += `
                        <label for="x${i}">Koefisien x${i}: </label>
                        <input type="number" id="x${i}" name="f_objektif[]" required>
                    `;
                }

                // Bagian Pembatas
                const pembatasDiv = document.getElementById("pembatas");
                pembatasDiv.innerHTML = "<h3>Pembatas</h3><p>Masukkan koefisien untuk pembatas:</p>";
                for (let i = 1; i <= m; i++) {
                    pembatasDiv.innerHTML += `
                        <fieldset>
                            <legend>Pembatas ${i}</legend>
                            <div id="constraint${i}">
                                ${Array.from({ length: n }, (_, j) => `
                                    <label for="constraint${i}_x${j + 1}">Koefisien x${j + 1}: </label>
                                    <input type="number" id="constraint${i}_x${j + 1}" name="constraints[${i - 1}][]" required>
                                `).join("")}
                                <br>
                                <label for="operator${i}">Operator: </label>
                                <select id="operator${i}" name="operators[]">
                                    <option value="<"><</option>
                                    <option value="≤">≤</option>
                                    <option value="=">=</option>
                                    <option value="≥">≥</option>
                                    <option value=">">></option>
                                </select>
                                <label for="rhs${i}">RHS: </label>
                                <input type="number" id="rhs${i}" name="rhs[]" value="0" readonly>
                            </div>
                        </fieldset>
                    `;
                }

                // Tampilkan tombol submit
                document.getElementById("submitButton").style.display = "block";
            } else {
                alert("Masukkan jumlah variabel dan pembatas dengan benar.");
            }
        }

        // Fungsi otomatis hitung RHS (contoh, jumlahkan semua koefisien sebagai default RHS)
        function autoHitungRHS() {
            const m = document.getElementById("m").value;
            const n = document.getElementById("n").value;
            for (let i = 1; i <= m; i++) {
                let rhsValue = 0;
                for (let j = 1; j <= n; j++) {
                    const value = parseFloat(document.getElementById(`constraint${i}_x${j}`).value) || 0;
                    rhsValue += value; // Aturan default: total semua koefisien
                }
                document.getElementById(`rhs${i}`).value = rhsValue;
            }
        }
    </script>
</head>
<body>
    <h1 style="text-align:center;">Kalkulator Metode Simpleks</h1>
    <p style="text-align:center;">Isi formulir berikut untuk memulai perhitungan metode Simpleks.</p>

    <form action="simplex.php" method="post" onsubmit="autoHitungRHS()">
        <!-- Input jumlah variabel dan pembatas -->
        <fieldset>
            <legend>Pengaturan Awal</legend>
            <label for="n">Jumlah Variabel Keputusan (n): </label>
            <input type="number" id="n" name="n" min="1" required>
            <br>
            <label for="m">Jumlah Pembatas (m): </label>
            <input type="number" id="m" name="m" min="1" required>
            <br>
            <button type="button" onclick="buatForm()">Buat Formulir</button>
        </fieldset>

        <!-- Bagian fungsi objektif -->
        <div id="fungsiObjektif"></div>

        <!-- Bagian pembatas -->
        <div id="pembatas"></div>

        <!-- Tombol submit -->
        <div id="submitButton" style="display:none; text-align:center;">
            <input type="submit" value="Proses Simpleks">
        </div>
    </form>
</body>
</html>
