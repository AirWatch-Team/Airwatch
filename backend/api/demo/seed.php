<?php
/**
 * AirWatch API Endpoint: Seed Synthetic Demo Data
 * Method: POST
 * Inserts 25 realistic synthetic records to quickly demonstrate system capabilities
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/aqi.php';

setCorsAndJsonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST request.', 405);
}

try {
    $db = Database::getConnection();

    $demoRecords = [
        ['Main Road', '2026-10-01', '08:30:00', 165, 84.50, 142.10, 28.5, 62.00, 'Vehicle Traffic', '[Synthetic Demo Data] Rush hour diesel exhaust emissions.'],
        ['College Campus', '2026-10-01', '09:15:00', 42, 11.20, 24.80, 27.0, 58.00, 'Other', '[Synthetic Demo Data] Clean air near tree grove.'],
        ['Bus Stand', '2026-10-01', '10:00:00', 148, 72.30, 128.90, 29.0, 60.00, 'Vehicle Traffic', '[Synthetic Demo Data] Heavy bus transit terminal.'],
        ['Market Yard', '2026-10-01', '11:45:00', 112, 52.80, 98.40, 31.2, 52.00, 'Dust', '[Synthetic Demo Data] Unpaved market area loading.'],
        ['Residential Area', '2026-10-01', '18:00:00', 58, 16.40, 35.10, 26.5, 65.00, 'Waste Burning', '[Synthetic Demo Data] Minor neighborhood smoke.'],

        ['Main Road', '2026-10-02', '08:45:00', 178, 92.10, 158.40, 29.1, 59.00, 'Vehicle Traffic', '[Synthetic Demo Data] Severe morning traffic jam.'],
        ['College Campus', '2026-10-02', '12:30:00', 38, 9.80, 21.30, 30.5, 50.00, 'Other', '[Synthetic Demo Data] Clear sunny sky.'],
        ['Industrial Zone', '2026-10-02', '14:00:00', 215, 125.60, 210.50, 33.0, 45.00, 'Industrial Smoke', '[Synthetic Demo Data] Heavy stack plume activity.'],
        ['Market Yard', '2026-10-02', '16:15:00', 125, 59.40, 105.00, 32.0, 48.00, 'Dust', '[Synthetic Demo Data] Wind-suspended particulates.'],
        ['Residential Area', '2026-10-02', '20:00:00', 64, 19.50, 39.80, 25.0, 70.00, 'Other', '[Synthetic Demo Data] Moderate ambient reading.'],

        ['Main Road', '2026-10-03', '09:00:00', 152, 78.40, 136.20, 28.0, 64.00, 'Vehicle Traffic', '[Synthetic Demo Data] High traffic density.'],
        ['College Campus', '2026-10-03', '11:00:00', 45, 12.50, 26.00, 29.0, 55.00, 'Other', '[Synthetic Demo Data] Satisfactory air quality.'],
        ['Bus Stand', '2026-10-03', '13:30:00', 135, 66.00, 118.50, 31.8, 51.00, 'Vehicle Traffic', '[Synthetic Demo Data] Regional bus transit.'],
        ['Construction Site B', '2026-10-03', '15:00:00', 185, 98.20, 172.00, 32.5, 46.00, 'Construction', '[Synthetic Demo Data] Excavation airborne dust.'],
        ['Residential Area', '2026-10-03', '19:30:00', 52, 14.80, 31.00, 26.0, 68.00, 'Other', '[Synthetic Demo Data] Favorable wind dispersion.'],

        ['Main Road', '2026-10-04', '08:15:00', 160, 81.00, 139.00, 27.5, 66.00, 'Vehicle Traffic', '[Synthetic Demo Data] Rush hour peak emissions.'],
        ['College Campus', '2026-10-04', '10:30:00', 35, 8.50, 19.20, 28.2, 57.00, 'Other', '[Synthetic Demo Data] Low humidity clear air.'],
        ['Bus Stand', '2026-10-04', '12:00:00', 140, 68.50, 122.00, 30.0, 54.00, 'Vehicle Traffic', '[Synthetic Demo Data] Passenger bus terminal.'],
        ['Industrial Zone', '2026-10-04', '14:45:00', 230, 140.00, 235.00, 34.0, 42.00, 'Industrial Smoke', '[Synthetic Demo Data] High sulfur dioxide & PM plume.'],
        ['Construction Site B', '2026-10-04', '16:30:00', 170, 89.00, 155.00, 31.5, 49.00, 'Construction', '[Synthetic Demo Data] Structural demolition dust.'],

        ['Market Yard', '2026-10-04', '17:45:00', 105, 48.00, 92.00, 30.0, 53.00, 'Waste Burning', '[Synthetic Demo Data] Open trash burning nearby.'],
        ['Residential Area', '2026-10-04', '20:15:00', 48, 13.00, 28.00, 24.5, 72.00, 'Other', '[Synthetic Demo Data] Good ambient night reading.'],
        ['Main Road', '2026-10-04', '21:00:00', 130, 62.00, 110.00, 26.0, 69.00, 'Vehicle Traffic', '[Synthetic Demo Data] Heavy truck freight corridor.'],
        ['College Campus', '2026-10-04', '22:00:00', 30, 7.00, 16.00, 24.0, 75.00, 'Other', '[Synthetic Demo Data] Quiet green zone.'],
        ['Industrial Zone', '2026-10-04', '23:00:00', 195, 108.00, 185.00, 25.5, 71.00, 'Industrial Smoke', '[Synthetic Demo Data] Night thermal inversion plume.']
    ];

    $query = "INSERT INTO air_quality_records 
              (location, record_date, record_time, aqi, pm25, pm10, temperature, humidity, pollution_source, notes, aqi_status) 
              VALUES 
              (:location, :record_date, :record_time, :aqi, :pm25, :pm10, :temperature, :humidity, :pollution_source, :notes, :aqi_status)";
    
    $stmt = $db->prepare($query);
    $insertedCount = 0;

    foreach ($demoRecords as $rec) {
        $status = getAQIStatus($rec[3]);
        $stmt->execute([
            ':location'        => $rec[0],
            ':record_date'     => $rec[1],
            ':record_time'     => $rec[2],
            ':aqi'             => $rec[3],
            ':pm25'            => $rec[4],
            ':pm10'            => $rec[5],
            ':temperature'     => $rec[6],
            ':humidity'        => $rec[7],
            ':pollution_source'=> $rec[8],
            ':notes'           => $rec[9],
            ':aqi_status'      => $status
        ]);
        $insertedCount++;
    }

    sendResponse(true, "Successfully inserted {$insertedCount} synthetic demo measurement records into MySQL database.", [
        'inserted_count' => $insertedCount,
        'disclaimer'     => 'Note: Demo data is synthetic and provided strictly for testing and presentation purposes.'
    ], 201);

} catch (PDOException $e) {
    sendError('Server error while inserting demo records.', 500);
}
