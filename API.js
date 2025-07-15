const admin = require('firebase-admin');
const serviceAccount = require('./serviceAccountKey.json');
const fs = require('fs').promises;

// Inisialisasi Firebase Admin SDK
admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
});

const db = admin.firestore();

// Fungsi untuk mengambil data dari Firestore
async function fetchDataFromFirestore() {
  try {
    const snapshot = await db.collection('staklim').get();
    const data = [];
    snapshot.forEach(doc => {
      data.push(doc.data());
    });
    return data;
  } catch (error) {
    console.error('Error fetching data from Firestore:', error);
    return null;
  }
}

// Fungsi untuk menyimpan data dalam bentuk JSON ke file
async function saveDataToJsonFile(data) {
  try {
    const jsonData = JSON.stringify(data, null, 2);
    await fs.writeFile('/home/jatg4813/public_html/data_hujan_terkini2.json', jsonData, { encoding: 'utf-8' });
    console.log('Data saved to data_hujan_terkini2.json');
  } catch (error) {
    console.error('Error saving data to file:', error);
  }
}

// Fungsi untuk menjalankan proses
async function runProcess() {
  const firestoreData = await fetchDataFromFirestore();
  if (firestoreData) {
    await saveDataToJsonFile(firestoreData);
  }
}

// Menjalankan proses setiap 15 menit
runProcess(); // Jalankan pertama kali langsung
