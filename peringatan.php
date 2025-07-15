<?php
// Fungsi untuk menentukan kategori hujan berdasarkan curah hujan (mm/hari)
function cekPeringatanHujan($rr) {
    // Memeriksa kategori hujan dan mengembalikan pesan peringatan yang sesuai
    if ($rr > 150) {
        return '<strong>Peringatan! Hujan Ekstrim</strong>';
    } elseif ($rr >= 100) {
        return '<strong>Peringatan! Hujan Sangat Lebat</strong>';
    } elseif ($rr >= 50) {
        return '<strong>Peringatan! Hujan Lebat</strong>';
    } else {
        return '';
    }
}

// Membaca file JSON untuk data hujan
$filePathHujan = 'data_hujan_terkini2.json'; // Pastikan path file sesuai
$jsonDataHujan = file_get_contents($filePathHujan);

// Mengonversi data JSON menjadi array PHP
$dataHujan = json_decode($jsonDataHujan, true);

// Membaca file JSON untuk metadata stasiun
$filePathMetadata = 'metadata.json'; // Pastikan path file sesuai
$jsonDataMetadata = file_get_contents($filePathMetadata);

// Mengonversi metadata JSON menjadi array PHP
$metadataStasiun = json_decode($jsonDataMetadata, true);

// Memeriksa apakah data berhasil dibaca
if ($dataHujan === null || $metadataStasiun === null) {
    die("Terjadi kesalahan saat membaca file JSON.");
}

$peringatanHujan = []; // Array untuk menyimpan pesan peringatan hujan yang relevan

// Menampilkan peringatan untuk setiap entri dalam data hujan
foreach ($dataHujan as $entry) {
    // Mengambil curah hujan (rr) dan ID stasiun untuk setiap entri
    $rr = $entry['rr'];
    $idStation = $entry['id'];
    $timestamp = $entry['timestamp']; // Menyimpan timestamp

    // Mengonversi timestamp ke format yang lebih mudah dibaca
    $date = new DateTime($timestamp, new DateTimeZone('UTC')); // Asumsikan timestamp awalnya dalam UTC
    $date->setTimezone(new DateTimeZone('Asia/Jakarta')); // Ubah ke zona waktu Asia/Jakarta (WIB)
    $formattedTimestamp = $date->format("Y-m-d H:i:s");
    
    // Memeriksa apakah ID stasiun ada di metadata
    if (isset($metadataStasiun[$idStation])) {
        $station = $metadataStasiun[$idStation];
        $namaKota = $station['nama_kota'];
        $nameStation = $station['name_station'];
    } else {
        $namaKota = 'Unknown';
        $nameStation = 'Unknown';
    }

    // Mendapatkan peringatan berdasarkan curah hujan
    $peringatan = cekPeringatanHujan($rr);

    // Menyimpan peringatan yang relevan dalam array
    if ($peringatan != '') {
        // Menambahkan ID stasiun, nama stasiun, nama kota, dan timestamp dalam pesan peringatan
        $peringatanHujan[] = [
            'peringatan' => $peringatan,
            'idStation' => $idStation,
            'nameStation' => $nameStation,
            'namaKota' => $namaKota,
            'timestamp' => $formattedTimestamp
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Peringatan Hujan</title>
    <style>
/* Modal Styling */
.modal {
    visibility: visible;
    opacity: 1;
    transition: opacity 0.5s ease, visibility 0.5s ease;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal.hidden {
    opacity: 0;
    visibility: hidden;
}


/* Konten Modal */
.modal-content {
    background-color: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    width: 75%; /* Lebih lebar */
    max-width: 900px; /* Batas maksimal */
    max-height: 80vh;
    overflow-y: auto;
    text-align: center;
    position: absolute;
    cursor: move;
}

/* Header Modal */
.modal-header {
    font-size: 26px;
    font-weight: bold;
    color: #d9534f;
    text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
    border-bottom: 3px solid #d9534f;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

/* Tombol Tutup */
.close-btn {
    background-color: #d9534f;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    margin-top: 15px;
}

.close-btn:hover {
    background-color: #c9302c;
}

/* Responsiveness */
@media (max-width: 768px) {
    .modal-content {
        width: 90%;
        max-width: 600px;
        padding: 20px;
    }

    .modal-header {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .modal-content {
        width: 95%;
        max-width: 500px;
        padding: 18px;
    }

    .modal-header {
        font-size: 20px;
    }
}

    /* Desain h2 agar lebih menarik */
    .peringatan {
        font-size: 2em;
        font-weight: bold;
        color: #d9534f; /* Warna merah yang lebih soft */
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        border-bottom: 3px solid #d9534f;
        display: inline-block;
        padding: 5px 15px;
        margin-bottom: 10px;
    }

    .namaKota {
        font-size: 2em;
        font-weight: bold;
        color: red;
    }

    .kondisiHujan {
        font-size: 1.5em;
        font-weight: bold;
    }

    .timestamp {
        font-size: 1.2em;
        color: grey;
    }

    /* Teks petunjuk di bawah modal */
    .info-text {
        font-size: 1.1em;
        text-align: center;
        margin-top: 10px;
        color: #333;
    }

    /* Responsiveness */
    @media (max-width: 768px) {
        .modal-content {
            width: 90%; /* Lebih lebar di layar kecil */
            max-width: 500px;
            padding: 20px;
        }

        .peringatan {
            font-size: 1.8em;
            padding: 4px 12px;
        }

        .namaKota {
            font-size: 1.8em;
        }

        .kondisiHujan {
            font-size: 1.3em;
        }

        .timestamp {
            font-size: 1.1em;
        }

        .info-text {
            font-size: 1em;
        }
    }

    @media (max-width: 480px) {
        .modal-content {
            width: 95%; /* Hampir penuh di layar kecil */
            max-width: 400px;
            padding: 15px;
        }

        .peringatan {
            font-size: 1.6em;
            padding: 3px 10px;
        }

        .namaKota {
            font-size: 1.6em;
        }

        .kondisiHujan {
            font-size: 1.2em;
        }

        .timestamp {
            font-size: 1em;
        }

        .info-text {
            font-size: 0.9em;
        }
    }


    </style>
</head>
<body>

    <div id="modal" class="modal">
        <div class="modal-content" id="modal-content">
            <!-- Isi popup akan ditambahkan disini -->
        </div>
    </div>

    <script>
 // Array dari PHP untuk peringatan hujan
const peringatanHujan = <?php echo json_encode($peringatanHujan); ?>;

// Fungsi untuk menampilkan modal dengan konten dinamis
function showModal(content) {
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');

    modalContent.innerHTML = `
        <h2 class="modal-header">⚠ Daftar Peringatan Hujan</h2>
        ${content}
        <button class="close-btn" onclick="closeModal()">Tutup</button>
    `;

    modal.style.display = "flex"; // Menampilkan modal
}

// Fungsi untuk menutup modal
function closeModal() {
    const modal = document.getElementById('modal');
    modal.classList.add('hidden');
}

// Jika ada peringatan, buat isi modal dan tampilkan
if (peringatanHujan.length > 0) {
    let modalContent = peringatanHujan.map(item => `
        <div class="peringatan">${item.peringatan}</div>
        <div class="kondisiHujan">
            (ID Stasiun: <strong>${item.idStation}</strong>, Nama Stasiun: <strong>${item.nameStation}</strong>)
        </div>
        <div class="namaKota">Kota: ${item.namaKota}</div>
        <div class="timestamp">Waktu Pengukuran: ${item.timestamp}</div>
        <hr />
    `).join('');

    showModal(modalContent);
}
if (peringatanHujan.length > 0) {
    let modalContent = peringatanHujan.map(item => `
        <div class="peringatan">${item.peringatan}</div>
        <div class="kondisiHujan">
            (ID Stasiun: <strong>${item.idStation}</strong>, Nama Stasiun: <strong>${item.nameStation}</strong>)
        </div>
        <div class="namaKota">Kota: ${item.namaKota}</div>
        <div class="timestamp">Waktu Pengukuran: ${item.timestamp}</div>
        <hr />
    `).join('');

    modalContent += `<div class="info-text">Modal akan tertutup otomatis dalam <span id="countdown">10</span> detik.</div>`;
    showModal(modalContent);

    // Update hitung mundur tiap detik
    let countdown = 10;
    const countdownInterval = setInterval(() => {
        countdown--;
        const countdownElement = document.getElementById('countdown');
        if (countdownElement) countdownElement.textContent = countdown;
        if (countdown <= 0) clearInterval(countdownInterval);
    }, 1000);

    // Tutup modal setelah 10 detik
    setTimeout(closeModal, 10000);
}


// Tutup modal jika pengguna klik di luar area modal
window.onclick = function(event) {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        closeModal();
    }
};

// Fungsi agar modal bisa digeser (drag & drop)
function dragElement(elmnt) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

    elmnt.onmousedown = dragMouseDown;
    elmnt.ontouchstart = dragMouseDown; // Untuk layar sentuh

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        
        // Ambil posisi awal klik/touch
        pos3 = e.clientX || e.touches[0].clientX;
        pos4 = e.clientY || e.touches[0].clientY;
        
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
        document.ontouchend = closeDragElement;
        document.ontouchmove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        
        // Hitung perbedaan posisi
        pos1 = pos3 - (e.clientX || e.touches[0].clientX);
        pos2 = pos4 - (e.clientY || e.touches[0].clientY);
        pos3 = e.clientX || e.touches[0].clientX;
        pos4 = e.clientY || e.touches[0].clientY;
        
        // Geser elemen modal
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
        document.ontouchend = null;
        document.ontouchmove = null;
    }
}

// Aktifkan drag pada modal setelah halaman dimuat
document.addEventListener("DOMContentLoaded", function() {
    dragElement(document.getElementById("modal-content"));
});

    </script>

</body>
</html>
