<script>
var map = L.map('peta').setView([-8.5580, 117.580], 9);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    <?php foreach($metadata as $md): ?>
        var rrValue = <?= $md['rr'] ?>;
        var markerColor = getColor(rrValue); // Mendapatkan warna marker berdasarkan nilai rr
        L.marker([<?= $md['lat'] ?>, <?= $md['long'] ?>], { icon: coloredMarker(markerColor) }).addTo(map)
            .bindPopup("Stasiun ID : <?= $md['id_sta'] ?><br>Nama Stasiun : <?= $md['nama_sta'] ?><br>Kota/Kab : <?= $md['nama_kota'] ?><br>Last Update: <?= $md['waktu'] ?> UTC<br>Curah : <?= $md['rr'] ?> mm <br>Tipe Alat: <?= $md['jenis_alat'] ?>");
    <?php endforeach; ?>
    // Fungsi untuk mendapatkan warna marker berdasarkan nilai rr
    function getColor(rr) {
        // Sesuaikan rentang nilai rr dengan warna yang diinginkan
        if (rr >= 0.5 && rr <= 20) { return 'green'; }// Warna hijau          
        else if (rr > 10 && rr <= 50) { return 'yellow'; } // Warna kuning   
        else if (rr > 50 && rr <= 100) {return 'orange';}
        else if (rr > 100 && rr <= 150){return 'red';}
        else if (rr > 150) {return 'purple';}
        else { return 'grey'; }
    }      
    // Fungsi untuk membuat ikon marker dengan warna tertentu
    function coloredMarker(color) {
        return L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + color + '.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
    }
      //Setting Carousel
    $(document).ready(function(){
      $('.owl-carousel-3').owlCarousel({
        loop:true,
        margin:10,
        autoplay:true,
        autoplayTimeout:5000,
        responsive:{
          0:{
            items:3
          },
          600:{
            items:3
          },
          1000:{
            items:3
          }
        }
      });
      $('.owl-carousel-2').owlCarousel({
        loop:true,
        margin:10,
        autoplay:true,
    autoplayTimeout:8000,
        responsive:{
          0:{
            items:1
          },
          600:{
            items:1
          },
          1000:{
            items:1
          }
        }
      });
    $('.owl-carousel-1').owlCarousel({
        loop:true,
        margin:10,
        autoplay: <?php echo ($count >= 2) ? 'true' : 'false'; ?>,
    autoplayTimeout:5000,
        responsive:{
          0:{
            items:2
          },
          600:{
            items:2
          },
          1000:{
            items:2
          }
        }
      });
    });
 //Setting Waktu
 function updateClock() {
  var now = new Date(); // Mendapatkan waktu saat ini
  var offsetWIB = 7;
  
  // Calculate WIB time
  var hoursWIB = ((now.getUTCHours() + offsetWIB) % 24).toString().padStart(2, '0');
  var hoursUTC = (now.getUTCHours()).toString().padStart(2, '0');
  var minutes = now.getUTCMinutes().toString().padStart(2, '0');
  var seconds = now.getUTCSeconds().toString().padStart(2, '0');
  
  // Format times
  var timeWIB = hoursWIB + ':' + minutes + ':' + seconds;
  var timeUTC = hoursUTC + ':' + minutes + ':' + seconds;
  
  // Display times
  document.getElementById('clock').innerHTML = timeUTC;
  document.getElementById('clock2').innerHTML = timeWIB;
  
  // Update every second
  setTimeout(updateClock, 1000);
}

// Memanggil fungsi updateClock saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
  updateClock();
});
  window.setTimeout( function() {
        window.location.reload();
        }   , 600000);
        function refreshPage() {
            var currentTime = new Date();
            var hours = currentTime.getHours().toString().padStart(2, '0');
            var minutes = currentTime.getMinutes().toString().padStart(2, '0');
            var seconds = currentTime.getSeconds().toString().padStart(2, '0');
            var timeString = hours + ':' + minutes + ':' + seconds;
            document.getElementById('last-refresh').innerHTML = 'Terakhir diperbarui pada: ' + timeString;
            setTimeout(refreshPage, 600000);
        }
        window.onload = function() {
            refreshPage();
        };
</script>