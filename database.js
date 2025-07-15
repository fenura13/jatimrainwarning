const mysql = require('mysql2/promise');
const moment = require('moment-timezone');
const fs = require('fs').promises;
const path = require('path');

async function saveRainfallData() {
    const dataFilePath = path.resolve('/home/jatg4813/public_html/data_hujan_terkini2.json');
    const metadataFilePath = path.resolve('/home/jatg4813/public_html/metadata.json');

    let metadata;
    try {
        const metadataContent = await fs.readFile(metadataFilePath, 'utf8');
        metadata = JSON.parse(metadataContent);
    } catch (err) {
        console.error('❌ Error reading metadata file:', err.message);
        return;
    }

    let records;
    try {
        const data = await fs.readFile(dataFilePath, 'utf8');
        records = JSON.parse(data);
        console.log(`✅ Successfully parsed JSON. Total records: ${records.length}`);
    } catch (err) {
        console.error('❌ Error reading or parsing rainfall JSON file:', err.message);
        return;
    }

    const dbConfig = {
        host: 'localhost',
        user: 'jatg4813_fenura1303',
        password: 'Fenura1302&',
        database: 'jatg4813_rainfall_data_db',
        port: 3306,
    };

    const intensities = [
        { label: "Tidak Hujan", min: 0, max: 0.5 },
        { label: "Hujan ringan", min: 0.5, max: 20.0 },
        { label: "Hujan sedang", min: 20.0, max: 50.0 },
        { label: "Hujan lebat", min: 50.0, max: 100.0 },
        { label: "Hujan sangat lebat", min: 100.0, max: 150.0 },
        { label: "Hujan ekstrim", min: 150.0, max: Infinity }
    ];

    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        await connection.beginTransaction();

        for (const record of records) {
            const id = String(record.id);
            const rr = parseFloat(record.rr);
            const rawTimestamp = record.timestamp.replace(' WIB', '');
            const timestamp = moment.tz(rawTimestamp, 'YYYY-MM-DD HH:mm:ss', 'Asia/Jakarta');
            
            if (!timestamp.isValid()) {
                console.warn(`⚠️ Invalid timestamp for record ID: ${id}`);
                continue;
            }
            
            const timestampFormatted = timestamp.format('YYYY-MM-DD HH:mm:ss');
            

            const stationMetadata = metadata[id];
            if (!stationMetadata) {
                console.warn(`⚠️ Metadata not found for station ID: ${id}`);
                continue;
            }

            // Nama tabel huruf kecil, hanya huruf/angka/underscore, maksimal 64 karakter
            const nama_stasiun = stationMetadata.name_station
                .toLowerCase()
                .replace(/[^a-z0-9_]/g, '_')
                .substring(0, 64);

            const name_station = stationMetadata.name_station || "Unknown";
            const latitude = parseFloat(stationMetadata.latitude) || 0;
            const longitude = parseFloat(stationMetadata.longitude) || 0;


            const intensitas = intensities.find(i => rr >= i.min && rr < i.max)?.label || "Tidak Diketahui";

            const nama_kota_kab = stationMetadata.nama_kota || "Unknown";
            const [rows] = await connection.execute(
                "SELECT id_kabkota FROM kabupaten_kota WHERE nama_kabkota = ? LIMIT 1",
                [nama_kota_kab]
            );
            const id_kabkota = rows.length > 0 ? rows[0].id_kabkota : null;

            if (!id_kabkota) {
                console.warn(`⚠️ id_kabkota not found for: ${nama_kota_kab}`);
                continue;
            }

            // Buat tabel jika belum ada
            const createTableQuery = `
                CREATE TABLE IF NOT EXISTS \`${nama_stasiun}\` (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    station_id VARCHAR(20) NOT NULL,
                    name_station VARCHAR(100) NOT NULL,
                    id_kabkota VARCHAR(50) NOT NULL,
                    latitude DECIMAL(10,6) NOT NULL,
                    longitude DECIMAL(10,6) NOT NULL,
                    timestamp TIMESTAMP NOT NULL,
                    rr FLOAT NOT NULL,
                    intensitas VARCHAR(50) NOT NULL,
                    UNIQUE KEY unique_entry (timestamp),
                    FOREIGN KEY (id_kabkota) REFERENCES kabupaten_kota(id_kabkota)
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB;
            `;
            await connection.execute(createTableQuery);

            // Simpan data ke tabel
            const insertQuery = `
                INSERT INTO \`${nama_stasiun}\`
                    (station_id, name_station, id_kabkota, latitude, longitude, timestamp, rr, intensitas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    rr = VALUES(rr), intensitas = VALUES(intensitas);
            `;
            await connection.execute(insertQuery, [
                id,
                name_station,
                id_kabkota,
                latitude,
                longitude,
                timestampFormatted,
                rr,
                intensitas
            ]);
            console.log(`✅ Record saved in table ${nama_stasiun}`);
        }

        await connection.commit();
        console.log('✅ All data has been saved.');
    } catch (err) {
        console.error('❌ Database error:', err.message);
        if (connection) await connection.rollback();
    } finally {
        if (connection) {
            await connection.end();
            console.log('🔒 Database connection closed.');
        }
    }
}

saveRainfallData();
