<?php
/**
 * AirWatch Input Validation & Sanitization Helpers
 */

/**
 * Validate and sanitize incoming Air Quality Record data
 *
 * @param array $data Raw input payload
 * @return array [is_valid => bool, errors => array, sanitized => array]
 */
function validateRecordInput(array $data): array {
    $errors = [];
    $sanitized = [];

    // 1. Location (Required, string, max 100 chars)
    $location = trim($data['location'] ?? '');
    if (empty($location)) {
        $errors['location'] = 'Location is required.';
    } elseif (strlen($location) > 100) {
        $errors['location'] = 'Location name must not exceed 100 characters.';
    } else {
        $sanitized['location'] = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
    }

    // 2. Date (Required, format YYYY-MM-DD)
    $date = trim($data['date'] ?? $data['record_date'] ?? '');
    if (empty($date)) {
        $errors['date'] = 'Measurement date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors['date'] = 'Invalid date format. Expected YYYY-MM-DD.';
    } else {
        $sanitized['record_date'] = $date;
    }

    // 3. Time (Optional/Default to current time or 12:00:00)
    $time = trim($data['time'] ?? $data['record_time'] ?? '');
    if (empty($time)) {
        $sanitized['record_time'] = date('H:i:s');
    } elseif (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
        $sanitized['record_time'] = strlen($time) === 5 ? $time . ':00' : $time;
    } else {
        $errors['time'] = 'Invalid time format. Expected HH:MM or HH:MM:SS.';
    }

    // 4. AQI (Required, numeric integer >= 0)
    if (!isset($data['aqi']) || $data['aqi'] === '') {
        $errors['aqi'] = 'AQI value is required.';
    } elseif (!is_numeric($data['aqi']) || (int)$data['aqi'] < 0 || (int)$data['aqi'] > 999) {
        $errors['aqi'] = 'AQI must be a non-negative integer (0-999).';
    } else {
        $sanitized['aqi'] = (int)$data['aqi'];
    }

    // 5. PM2.5 (Required, numeric >= 0)
    if (!isset($data['pm25']) || $data['pm25'] === '') {
        $errors['pm25'] = 'PM2.5 concentration is required.';
    } elseif (!is_numeric($data['pm25']) || (float)$data['pm25'] < 0) {
        $errors['pm25'] = 'PM2.5 concentration must be a positive number.';
    } else {
        $sanitized['pm25'] = round((float)$data['pm25'], 2);
    }

    // 6. PM10 (Required, numeric >= 0)
    if (!isset($data['pm10']) || $data['pm10'] === '') {
        $errors['pm10'] = 'PM10 concentration is required.';
    } elseif (!is_numeric($data['pm10']) || (float)$data['pm10'] < 0) {
        $errors['pm10'] = 'PM10 concentration must be a positive number.';
    } else {
        $sanitized['pm10'] = round((float)$data['pm10'], 2);
    }

    // 7. Temperature (Optional, numeric -50 to 60 °C)
    if (isset($data['temperature']) && $data['temperature'] !== '' && $data['temperature'] !== null) {
        if (!is_numeric($data['temperature']) || (float)$data['temperature'] < -50 || (float)$data['temperature'] > 60) {
            $errors['temperature'] = 'Temperature must be a number between -50°C and 60°C.';
        } else {
            $sanitized['temperature'] = round((float)$data['temperature'], 1);
        }
    } else {
        $sanitized['temperature'] = null;
    }

    // 8. Humidity (Optional, numeric 0 to 100 %)
    if (isset($data['humidity']) && $data['humidity'] !== '' && $data['humidity'] !== null) {
        if (!is_numeric($data['humidity']) || (float)$data['humidity'] < 0 || (float)$data['humidity'] > 100) {
            $errors['humidity'] = 'Humidity must be a value between 0% and 100%.';
        } else {
            $sanitized['humidity'] = round((float)$data['humidity'], 2);
        }
    } else {
        $sanitized['humidity'] = null;
    }

    // 9. Pollution Source (Optional, default 'Other')
    $source = trim($data['pollution_source'] ?? 'Other');
    $validSources = ['Vehicle Traffic', 'Construction', 'Waste Burning', 'Industrial Smoke', 'Dust', 'Other'];
    if (empty($source) || !in_array($source, $validSources)) {
        $sanitized['pollution_source'] = 'Other';
    } else {
        $sanitized['pollution_source'] = $source;
    }

    // 10. Notes (Optional, string)
    $notes = trim($data['notes'] ?? '');
    $sanitized['notes'] = empty($notes) ? null : htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');

    return [
        'is_valid'  => empty($errors),
        'errors'    => $errors,
        'sanitized' => $sanitized
    ];
}
