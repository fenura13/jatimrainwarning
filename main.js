const { exec } = require('child_process');
const path = require('path');

// Gunakan path absolut ke node binary dari environment Anda
const NODE_PATH = '/home/jatg4813/nodevenv/running/main.js/20/bin/node';

function runScript(scriptPath) {
    return new Promise((resolve, reject) => {
        exec(`${NODE_PATH} ${scriptPath}`, (error, stdout, stderr) => {
            if (error) {
                console.error(`❌ Error in ${scriptPath}:`, error);
                return reject(error);
            }
            if (stderr) {
                console.warn(`⚠️ Warning in ${scriptPath}:`, stderr);
            }
            console.log(`✅ Output from ${scriptPath}:\n${stdout}`);
            resolve();
        });
    });
}

async function runSequence() {
    try {
        console.log(`[${new Date().toISOString()}] 🕒 Menjalankan proses berurutan...`);

        await runScript('/home/jatg4813/running/runjobs.js/runjobs.js');
        await runScript('/home/jatg4813/running/API.js/API.js');
        await runScript('/home/jatg4813/running/database.js/database.js');

        const utcHour = new Date().getUTCHours();
        if (utcHour >= 23 || utcHour < 1) {
            console.log('⏰ Waktu cocok untuk menjalankan historis.js (23:00–01:00 UTC / 06:00–08:00 WIB)');
            await runScript('/home/jatg4813/nodevenv/running/historis.js/historis.js');
        } else {
            console.log('⏳ Bukan waktu untuk menjalankan historis.js');
        }

        console.log('✅ Semua proses selesai.\n');
    } catch (err) {
        console.error('🚨 Terjadi kesalahan saat menjalankan pipeline:', err);
    }
}

// Jalankan pertama kali
runSequence();
