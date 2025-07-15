<script>
      //Setting Carousel
    $(document).ready(function(){
      $('.owl-carousel-3').owlCarousel({
        loop:true,
        margin:10,
        autoplay: <?php  echo ($counton >= 3) ? 'true' : 'false'; ?>,
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
            document.getElementById('last-refresh').innerHTML = 'Terakhir diperbarui pada: ' + timeString +'  WIB';
            setTimeout(refreshPage, 600000);
        }
        window.onload = function() {
            refreshPage();
        };
</script>