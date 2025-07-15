const fs = require('fs');
const mysql = require('mysql2');

// Konfigurasi koneksi database
const db = mysql.createConnection({
    host: 'localhost',
    user: 'jatg4813_fenura1303',
    password: 'Fenura1302&',
    database: 'jatg4813_rainfall_data_db',
});

// Fungsi menentukan peringatan dari curah hujan
function getPeringatanHujan(rr) {
    if (rr > 150) {
        return 'Peringatan! Hujan Ekstrim';
    } else if (rr >= 100) {
        return 'Peringatan! Hujan Sangat Lebat';
    } else if (rr >= 50) {
        return 'Peringatan! Hujan Lebat';
    } else {
        return ''; // Tidak ada peringatan
    }
}

// Fungsi untuk menjalankan proses setiap hari antara jam 23:00 hingga 01:00
function checkTimeAndRun() {
    const currentHour = new Date().getHours(); // Mendapatkan jam saat ini

    // Jika jam saat ini berada antara 23:00 dan 01:00
    if (currentHour >= 23 || currentHour < 1) {
        // Baca file JSON
        fs.readFile('/home/jatg4813/running/API.js/data_hujan_terkini2.json', 'utf8', (err, jsonData) => {
            if (err) {
                console.error('Gagal membaca file JSON:', err);
                return;
            }

            let dataList = JSON.parse(jsonData);

            // Jika data berupa array
            if (!Array.isArray(dataList)) {
                // Jika berupa object yang key-nya adalah ID stasiun
                dataList = Object.values(dataList);
            }

            dataList.forEach(data => {
                const { id, rr, timestamp } = data;
                const jam = new Date(timestamp).toISOString().substring(11, 16); // Format HH:mm dari UTC

                if (jam === '23:50') {
                    const nilaiRR = parseFloat(rr);
                    const peringatan = getPeringatanHujan(nilaiRR);

                    if (peringatan !== '') {
                        const sql = `
                            INSERT INTO historis_peringatan (kode_stasiun, waktu, rr, peringatan)
                            VALUES (?, ?, ?, ?)
                        `;
                        const values = [id, timestamp, nilaiRR, peringatan];

                        db.execute(sql, values, (err) => {
                            if (err) {
                                console.error(`Gagal menyimpan data untuk ${id}:`, err);
                            } else {
                                console.log(`Data peringatan untuk ${id} disimpan.`);
                            }
                        });
                    }
                }
            });
        });
    }
}

// Jalankan proses setiap menit
setInterval(checkTimeAndRun, 60 * 1000); // Cek setiap menit
