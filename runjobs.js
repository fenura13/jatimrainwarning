(async () => {
    const admin = require('firebase-admin');
    const credentials = require("./serviceAccountKey.json");
    admin.initializeApp({
        credential: admin.credential.cert(credentials)
    });
  
    const db = admin.firestore();
    const fetch = (await import('node-fetch')).default;
    const now = new Date();
    const day = String(now.getUTCDate()).padStart(2, '0');
    const month = String(now.getUTCMonth() + 1).padStart(2, '0');
    const formattedDate = `${day}-${month}-${now.getFullYear()}`;
    const baseUrl = 'http://202.90.198.212/logger';
    const urls = [
        `/log-${formattedDate}.txt`,
        `/logAAWS-${formattedDate}.txt`,
        `/ftp/logARG-${formattedDate}.txt`,
        `/ftp/logAWS-${formattedDate}.txt`,
        `/ftp/logAAWS-${formattedDate}.txt`,
    ];
    const targetStats = {
        'log-': [
            "STA0196", "STG1070",
            "150036", "STA0183", "STA0071", "150042",
            "STA0265", "STA0192", "150037", "150311",
            "150033", "STA0198", "150046", "STA0116",
            "150314", "150039", "150035",
            "STA0061", "STA0069", "150041", "STA0187",
            "STA0247", "STA0060", "STA0189", "STG1006",
            "STA0191", "STA0064", "STA0163", "150315",
            "150312", "14032806", "STA0248", "STA0246",
            "STA0249", "STA0070", "150043", "150038",
            "sta3219", "150047", "STA0162", "STA0193",
            "150044", "150034", "STA0072", "150313",
            "150045", "150032", "150040",
             "STG1071", "STG1072", "STG1074",
            "STG1073", "STG1044", "STG1045", "STG1046",
            "STG1047", "STG1048", "STG1049", "STG1050",
            "STG1051", "STG1052", "STG1053", "STG1054",
            "STG1055", "STG1056", "STG1057", "STG1058",
            "STG1059", "STG1060", "STG1061", "STA0063"
            , "150316", "STA0188" , "14032808", "STA0184", "STA0062", "STA0194", "STA0074"
          ],
        
        'logAAWS-': [
            'aaws0359', 'aaws0336', 
            'STA3052', 'sta3051', 'sta3054', 
            'STA3053', 'sta3057', 'STW1059'],

        'ftp/logARG': [
            '14032807', 
            'STA0065', 'STA0185'
            ],
                    
        'ftp/logAWS': [
            'STA3221', 'STA2113',
            'STW1029', 'STA2058', 'STA2293', 'STA4006', 
            'STA2109', 'STA2170', 'STA2072', 'STA4007', 
            'STA4005', 'STA2243', 'STA2057', 'STW1016', 
            'STA2156', 'STA2141', 'STW1017', 'STA2283', 
            'STA2103', 'STA2104', '160050', 'STW1070'],

    };
  
    async function updateDataInFirestore(idsta, newData) {
      try {
          // Filter out any undefined fields in newData before updating
          const filteredData = Object.fromEntries(
              Object.entries(newData).filter(([key, value]) => value !== undefined)
          );
  
          if (Object.keys(filteredData).length > 0) {
              const docRef = db.collection('staklim').doc(idsta);
              await docRef.update(filteredData);
              console.log(`Data for ${idsta} successfully updated.`);
          } else {
              console.log(`No valid data to update for ${idsta}. Skipping update.`);
          }
      } catch (error) {
          console.error(`Error updating data for ${idsta} in Firestore:`, error);
      }
  }
  
    async function updateFirestoreWithData(transformedData) {
        try {
            for (const idsta in transformedData) {
                const newData = transformedData[idsta];
                await updateDataInFirestore(idsta, newData);
            }
            console.log('Data successfully updated in Firestore.');
        } catch (error) {
            console.error('Error updating data in Firestore:', error);
        }
    }
  
  async function fetchDataWithRetry(url, retries = 3, delay = 1000) {
      let attempt = 0;
      while (attempt < retries) {
          try {
              const response = await fetch(url, { timeout: 10000 }); // Set timeout to 10 seconds
              if (!response.ok) {
                  throw new Error(`Response not ok: ${response.statusText}`);
              }
              return await response.text();
          } catch (error) {
              attempt++;
              console.error(`Attempt ${attempt} failed: ${error.message}`);
              if (attempt < retries) {
                  console.log(`Retrying in ${delay}ms...`);
                  await new Promise(resolve => setTimeout(resolve, delay)); // Wait before retry
              } else {
                  throw new Error(`Failed after ${retries} attempts`);
              }
          }
      }
  }
  
  async function fetchData(url) {
      try {
          const data = await fetchDataWithRetry(`${baseUrl}${url}`);
          return data ? data.split('\n').map(line => line.replace(/\r$/, '')) : [];
      } catch (error) {
          console.error(`Error fetching data from ${url}: ${error.message}`);
          return [];
      }
  }
  
    async function fetchAndCombineData(urls, targetStats) {
        const combinedData = {};
  
        for (const url of urls) {
            const data = await fetchData(url);
            const category = Object.keys(targetStats).find(key => url.includes(key));
  
            if (data.length > 0 && category) {
                const targetStas = targetStats[category];
                const staData = {};
  
                targetStas.forEach(sta => {
                    staData[sta] = null;
                });
  
                data.forEach(line => {
                    targetStas.forEach(sta => {
                      if (line.includes(sta)) {
                        if (!(category === 'ftp/logAAWS' && sta === 'STW1059' && url.endsWith(`/ftp/logAAWS-${formattedDate}.txt`))) {
                          staData[sta] = line;
                        }
                      }
                    });
                  });
  
                const filteredStaData = Object.fromEntries(
                    Object.entries(staData).filter(([_, value]) => value !== null)
                );
  
                combinedData[url] = filteredStaData;
            }
        }
        return combinedData;
    }
  
    function transformLogARGData(data) {
      const transformedData = {};
  
      for (const id in data) {
          const entry = data[id];
  
          if (entry !== null) {
            if (
                id === 'STG1073' || id === 'STG1070' || id === 'STA0196' ||
                id === '150036' || id === 'STA0183' || id === 'STA0071' || id === '150042' ||
                id === 'STA0265' || id === 'STA0192' || id === '150037' || id === '150311' ||
                id === '150033' || id === 'STA0198' || id === '150046' || id === 'STA0116' ||
                id === '150314' || id === '150039' || id === '150035' || id === 'STA0061' ||
                id === 'STA0069' || id === '150041' || id === 'STA0187' || id === 'STA0247' ||
                id === 'STA0060' || id === 'STA0189' || id === 'STG1006' || id === 'STA0191' ||
                id === 'STA0064' || id === 'STA0163' || id === '150315' || id === '150312' ||
                id === '14032806' || id === 'STA0248' || id === 'STA0246' || id === 'STA0249' ||
                id === 'STA0070' || id === '150043' || id === '150038' || id === 'sta3219' ||
                id === '150047' || id === 'STA0162' || id === 'STA0193' || id === '150044' ||
                id === '150034' || id === 'STA0072' || id === '150313' || id === '150045' ||
                id === '150032' || id === '150040' ||
                id === 'STG1071' || id === 'STG1072' || id === 'STG1074' || id === 'STG1044' ||
                id === 'STG1045' || id === 'STG1046' || id === 'STG1047' || id === 'STG1048' ||
                id === 'STG1049' || id === 'STG1050' || id === 'STG1051' || id === 'STG1052' ||
                id === 'STG1053' || id === 'STG1054' || id === 'STG1055' || id === 'STG1056' ||
                id === 'STG1057' || id === 'STG1058' || id === 'STG1059' || id === 'STG1060' || 
                id === 'STG1061' || id === 'STA0063' || id === '150316'  || id === 'STA0188' || id === '14032808' || id === 'STA0184' || id === 'STA0062' || id === 'STA0194'|| id === 'STA0074'
            ) {
                  const dataArray = entry.split(';');
                  const rr = dataArray[2];
                  const timestamp = parseTimestamplog(dataArray);
      
                      transformedData[id] = {
                          id,
                          timestamp,
                          rr,
                      };
  
                } else if (
                    id.startsWith('14032807') ||
                    id.startsWith('STA0065') || id.startsWith('STA0185')
                ) {
                // Helper: konversi komponen waktu UTC ke string WIB "YYYY-MM-DD hh:mm:ss WIB"
                function convertToWIB(year, month, day, hour, minute, second = 0) {
                const dateUTC = new Date(Date.UTC(
                    parseInt(year, 10),
                    parseInt(month, 10) - 1,
                    parseInt(day, 10),
                    parseInt(hour, 10),
                    parseInt(minute, 10),
                    parseInt(second, 10)
                ));
                const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
                const pad = n => String(n).padStart(2, '0');
                const y   = dateWIB.getUTCFullYear();
                const m   = pad(dateWIB.getUTCMonth() + 1);
                const d   = pad(dateWIB.getUTCDate());
                const h   = pad(dateWIB.getUTCHours());
                const min = pad(dateWIB.getUTCMinutes());
                const s   = pad(dateWIB.getUTCSeconds());
                return `${y}-${m}-${d} ${h}:${min}:${s} WIB`;
                }

                // …di dalam loop/parsing:
                const dataArray   = entry.split(',');
                const inputString = dataArray[0];
                const datasplit   = inputString.split(' ');
                const dateElement = datasplit.find(el => el.includes('/'));
                const dateParts   = dateElement.split('/');         // [day, month, year]

                // time string misal "14:30:15"
                const timeParts   = dataArray[1].split(':');
                const [hour, minute, second] = timeParts;

                // konversi ke WIB
                const timestamp = convertToWIB(
                dateParts[2], // year
                dateParts[1], // month
                dateParts[0], // day
                hour,
                minute,
                second
                );

                const rr = dataArray[2];
                transformedData[id] = { id, timestamp, rr };


                }
                else if (id === 'STA0188' ) {
                // Helper: konversi UTC → WIB (GMT+7)
                function convertToWIB(year, month, day, hour, minute, second = 0) {
                const dateUTC = new Date(Date.UTC(
                    parseInt(year, 10),
                    parseInt(month, 10) - 1,
                    parseInt(day, 10),
                    parseInt(hour, 10),
                    parseInt(minute, 10),
                    parseInt(second, 10)
                ));
                const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
                const pad = n => String(n).padStart(2, '0');
                return `${dateWIB.getUTCFullYear()}-${pad(dateWIB.getUTCMonth()+1)}-${pad(dateWIB.getUTCDate())} `
                    + `${pad(dateWIB.getUTCHours())}:${pad(dateWIB.getUTCMinutes())}:${pad(dateWIB.getUTCSeconds())} WIB`;
                }

                const dataArray = entry.split(' ');
                const datePart = dataArray[1].split(',')[0].replace(/"/g, '');  // "12/11/2024"
                const timePart = dataArray[1].split(',')[1].replace(/"/g, '');  // "04:00:00"

                const [day, month, year] = datePart.split('/');             // ["12","11","2024"]
                const [hour, minute, second] = timePart.split(':');         // ["04","00","00"]

                // konversi ke WIB
                const timestamp = convertToWIB(year, month, day, hour, minute, second);

                const rr = dataArray[2];
                transformedData[id] = {
                id,
                timestamp,
                rr
                };

                    

                }   else if (id==='aaws0359'||id==='aaws0336'){
                    // Helper: konversi UTC → WIB (GMT+7)
                    function convertToWIB(year, month, day, hour, minute, second = 0) {
                    const dateUTC = new Date(Date.UTC(
                        parseInt(year, 10),
                        parseInt(month, 10) - 1,
                        parseInt(day, 10),
                        parseInt(hour, 10),
                        parseInt(minute, 10),
                        parseInt(second, 10)
                    ));
                    const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
                    const pad = n => String(n).padStart(2, '0');
                    return `${dateWIB.getUTCFullYear()}-${pad(dateWIB.getUTCMonth()+1)}-${pad(dateWIB.getUTCDate())} `
                        + `${pad(dateWIB.getUTCHours())}:${pad(dateWIB.getUTCMinutes())}:${pad(dateWIB.getUTCSeconds())} WIB`;
                    }
                    const dataArray = entry.split(';');
                    const timestampData = dataArray[0].split('.')[0];

                    const year   = timestampData.slice(8, 12);
                    const month  = timestampData.slice(12, 14);
                    const day    = timestampData.slice(14, 16);
                    const hour   = timestampData.slice(16, 18);
                    const minute = timestampData.slice(18);

                    // Pakai helper untuk dapat string WIB
                    const timestamp = convertToWIB(year, month, day, hour, minute);

                    const rr = dataArray[8];
                    transformedData[id] = {
                    id,
                    timestamp,
                    rr,
                    };

  
        
                } else if (id==='sta3051'||id==='sta3054'||id==='sta3057'){
                    // Helper: konversi UTC → WIB (GMT+7)
                    function convertToWIB(year, month, day, hour, minute, second = 0) {
                    const dateUTC = new Date(Date.UTC(
                        parseInt(year, 10),
                        parseInt(month, 10) - 1,
                        parseInt(day, 10),
                        parseInt(hour, 10),
                        parseInt(minute, 10),
                        parseInt(second, 10)
                    ));
                    const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
                    const pad = n => String(n).padStart(2, '0');
                    return `${dateWIB.getUTCFullYear()}-${pad(dateWIB.getUTCMonth()+1)}-${pad(dateWIB.getUTCDate())} `
                        + `${pad(dateWIB.getUTCHours())}:${pad(dateWIB.getUTCMinutes())}:${pad(dateWIB.getUTCSeconds())} WIB`;
                    }
                const dataArray     = entry.split(';');
                const timestampData = dataArray[0].split('.')[0];

                const year   = timestampData.slice(7, 11);
                const month  = timestampData.slice(11, 13);
                const day    = timestampData.slice(13, 15);
                const hour   = timestampData.slice(15, 17);
                const minute = timestampData.slice(17);

                // Konversi ke WIB dengan helper
                const timestamp = convertToWIB(year, month, day, hour, minute);

                const rr = dataArray[8];
                transformedData[id] = {
                id,
                timestamp,
                rr,
                };

  
    
                }else if (id === 'STA3052' || id === 'STA3053') {
                                        // Helper: konversi UTC → WIB (GMT+7)
                    function convertToWIB(year, month, day, hour, minute, second = 0) {
                    const dateUTC = new Date(Date.UTC(
                        parseInt(year, 10),
                        parseInt(month, 10) - 1,
                        parseInt(day, 10),
                        parseInt(hour, 10),
                        parseInt(minute, 10),
                        parseInt(second, 10)
                    ));
                    const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
                    const pad = n => String(n).padStart(2, '0');
                    return `${dateWIB.getUTCFullYear()}-${pad(dateWIB.getUTCMonth()+1)}-${pad(dateWIB.getUTCDate())} `
                        + `${pad(dateWIB.getUTCHours())}:${pad(dateWIB.getUTCMinutes())}:${pad(dateWIB.getUTCSeconds())} WIB`;
                    }

                    const dataArray     = entry.split(';');
                    const timestampData = dataArray[0]; // Ambil bagian yang mengandung ID dan timestamp

                    // Ekstrak timestamp dari format: STA3053202411112350
                    const match = timestampData.match(/STA305[23](\d{12})/);
                    if (match) {
                    const rawTimestamp = match[1]; // "202411112350"
                    const year   = rawTimestamp.slice(0, 4);
                    const month  = rawTimestamp.slice(4, 6);
                    const day    = rawTimestamp.slice(6, 8);
                    const hour   = rawTimestamp.slice(8, 10);
                    const minute = rawTimestamp.slice(10, 12);

                    // Gunakan helper untuk konversi ke WIB
                    const timestamp = convertToWIB(year, month, day, hour, minute);

                    // Ambil nilai RR dari kolom ke-9 (indeks 8)
                    const rr = dataArray[8];

                    transformedData[id] = {
                        id,
                        timestamp,
                        rr,
                    };
                    }

                      

            } else if (
                id.startsWith('STA3221') || id.startsWith('STA2113') || 
                id.startsWith('STW1029') || id.startsWith('STA2058') || id.startsWith('STA2293') || id.startsWith('STA4006') ||
                id.startsWith('STA2109') || id.startsWith('STA2170') || id.startsWith('STA2072') || id.startsWith('STA4007') ||
                id.startsWith('STA4005') || id.startsWith('STA2243') || id.startsWith('STA2057') || id.startsWith('STW1016') ||
                id.startsWith('STA2156') || id.startsWith('STA2141') || id.startsWith('STW1017') || id.startsWith('STA2283') ||
                id.startsWith('STA2103') || id.startsWith('STA2104') || id.startsWith('160050')  || id.startsWith('STW1070')
            ) {
                                    // Helper: konversi UTC → WIB (GMT+7)
                    function convertToWIB(year, month, day, hour, minute, second = 0) {
                    const dateUTC = new Date(Date.UTC(
                        parseInt(year, 10),
                        parseInt(month, 10) - 1,
                        parseInt(day, 10),
                        parseInt(hour, 10),
                        parseInt(minute, 10),
                        parseInt(second, 10)
                    ));
                    const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
                    const pad = n => String(n).padStart(2, '0');
                    return `${dateWIB.getUTCFullYear()}-${pad(dateWIB.getUTCMonth()+1)}-${pad(dateWIB.getUTCDate())} `
                        + `${pad(dateWIB.getUTCHours())}:${pad(dateWIB.getUTCMinutes())}:${pad(dateWIB.getUTCSeconds())} WIB`;
                    }
  // Cek dulu apakah entry ada dan bukan string kosong atau whitespace
  if (!entry || typeof entry !== 'string' || entry.trim() === '') {
    console.warn(`Entry kosong atau tidak valid untuk ID ${id}, dilewati.`);
    // skip proses ini
  } else {
    const dataArray = entry.split(',');

    // Minimal harus ada 11 kolom sesuai format
    if (dataArray.length < 11) {
      console.warn(`Entry ID ${id} tidak memiliki cukup kolom:`, dataArray);
      // skip
    } else {
      try {
        // Ambil tanggal dan waktu, dengan perlindungan error split dan index
        const datePart = dataArray[0].split('"')[1];  // contoh: "14/10/2024"
        const timePart = dataArray[1].split('"')[1];  // contoh: "00:40:00"

        if (!datePart || !timePart) {
          console.warn(`Format tanggal/waktu tidak valid untuk ID ${id}:`, dataArray[0], dataArray[1]);
          // skip
        } else {
          const [day, month, year] = datePart.split('/');
          const [hour, minute, second = '00'] = timePart.split(':');

          // Pastikan semua komponen tanggal & waktu ada
          if (!day || !month || !year || !hour || !minute) {
            console.warn(`Komponen tanggal/waktu tidak lengkap untuk ID ${id}:`, datePart, timePart);
            // skip
          } else {
            const timestamp = convertToWIB(year, month, day, hour, minute, second);

            const rr = dataArray[10].replace(/"/g, '').trim();

            transformedData[id] = {
              id,
              timestamp,
              rr
            };
          }
        }
      } catch (err) {
        console.error(`Error memproses entry ID ${id}:`, err.message);
        // skip
      }
    }
  }
}   else if ( id.startsWith('STW1059')
              ) {
                
                const dataArray = entry.split(';');

                // Extracting the raw timestamp
                const rawTimestamp = dataArray[1];  // Example: '14112024074003'
                const day = rawTimestamp.slice(0, 2); // '14'
                const month = rawTimestamp.slice(2, 4); // '11'
                const year = rawTimestamp.slice(4, 8); // '2024'
                const hour = rawTimestamp.slice(8, 10); // '08'
                const minute = rawTimestamp.slice(10, 12); // '30'
                const second = rawTimestamp.slice(12, 14); // '00'
                
                // Reformat the date into YYYY-MM-DD
                const formattedDate = `${year}-${month}-${day}`;
                
                // Reformat the time into HH:mm:ss
                const formattedTime = `${hour}:${minute}:${second}`;
                
                const formattedTimestamp = `${formattedDate} ${formattedTime} UTC`;
                
                // Assuming 'rr' is the 10th value in the data array
                const rr = dataArray[9];
                
                // Extract the ID (assuming it's the first value in the array)
                const id = dataArray[0];
                
                transformedData[id] = { id, timestamp: formattedTimestamp, rr };
                 
              }
          }
      }
  
      return transformedData;
  }
  
  
        // Helper: konversi komponen waktu UTC ke string WIB "YYYY-MM-DD hh:mm:ss WIB"
        function convertToWIB(year, month, day, hour, minute, second = 0) {
        // Buat objek UTC
        const dateUTC = new Date(Date.UTC(
            parseInt(year, 10),
            parseInt(month, 10) - 1,
            parseInt(day, 10),
            parseInt(hour, 10),
            parseInt(minute, 10),
            parseInt(second, 10)
        ));
        // Tambah offset +7 jam
        const dateWIB = new Date(dateUTC.getTime() + 7 * 60 * 60 * 1000);
        
        // Padding dua digit
        const pad = n => String(n).padStart(2, '0');
        
        // Ambil kembali komponen via getUTC* (agar tidak terpengaruh TZ runtime)
        const y   = dateWIB.getUTCFullYear();
        const m   = pad(dateWIB.getUTCMonth() + 1);
        const d   = pad(dateWIB.getUTCDate());
        const h   = pad(dateWIB.getUTCHours());
        const min = pad(dateWIB.getUTCMinutes());
        const s   = pad(dateWIB.getUTCSeconds());
        
        return `${y}-${m}-${d} ${h}:${min}:${s} WIB`;
        }
  
    // Helper parsing functions
        function parseTimestamplog(dataArray) {
        // sekarang kamu bisa panggil convertToWIB(...)
        const ts = dataArray[1];
        const year    = ts.slice(4, 8);
        const month   = ts.slice(2, 4);
        const day     = ts.slice(0, 2);
        const hours   = ts.slice(8, 10);
        const minutes = ts.slice(10, 12);
        const seconds = ts.slice(12, 14);
        return convertToWIB(year, month, day, hours, minutes, seconds);
        }
  
  
    const runJob = async () => {
      try {
        const combinedData = await fetchAndCombineData(urls, targetStats);
        const allData = {};
        for (const url in combinedData) {
          const staData = combinedData[url];
          for (const sta in staData) {
            allData[sta] = staData[sta];
          }
        }
        const transformedData = transformLogARGData(allData);
        await updateFirestoreWithData(transformedData);
      } catch (error) {
        console.error(`Error: ${error.message}`);
      }
    };
    
    // Jalankan pertama kali
    runJob();
    
  })();
  