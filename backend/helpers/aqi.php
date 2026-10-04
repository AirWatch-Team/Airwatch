<?php
/**
 * AirWatch AQI Classification Engine
 * Standardized US EPA AQI Category Mapping
 */

/**
 * Classify AQI numerical value into documented status category
 *
 * @param int $aqi
 * @return string Status label
 */
function getAQIStatus(int $aqi): string {
    if ($aqi <= 50) {
        return 'Good';
    } elseif ($aqi <= 100) {
        return 'Moderate';
    } elseif ($aqi <= 150) {
        return 'Unhealthy for Sensitive Groups';
    } elseif ($aqi <= 200) {
        return 'Unhealthy';
    } elseif ($aqi <= 300) {
        return 'Very Unhealthy';
    } else {
        return 'Hazardous';
    }
}

/**
 * Get full AQI Classification details including color codes and health advisories
 *
 * @param int $aqi
 * @return array
 */
function getAQIDetails(int $aqi): array {
    $status = getAQIStatus($aqi);
    
    switch ($status) {
        case 'Good':
            return [
                'aqi' => $aqi,
                'status' => 'Good',
                'level' => 1,
                'color' => '#10b981', // Fresh green
                'bg_color' => '#ecfdf5',
                'border_color' => '#a7f3d0',
                'health_advisory' => 'Air quality is satisfactory, and air pollution poses little or no risk.',
                'alert' => false
            ];
        case 'Moderate':
            return [
                'aqi' => $aqi,
                'status' => 'Moderate',
                'level' => 2,
                'color' => '#d97706', // Amber/Yellow
                'bg_color' => '#fef3c7',
                'border_color' => '#fde68a',
                'health_advisory' => 'Air quality is acceptable; however, sensitive individuals may experience minor irritation.',
                'alert' => false
            ];
        case 'Unhealthy for Sensitive Groups':
            return [
                'aqi' => $aqi,
                'status' => 'Unhealthy for Sensitive Groups',
                'level' => 3,
                'color' => '#ea580c', // Orange
                'bg_color' => '#ffedd5',
                'border_color' => '#fed7aa',
                'health_advisory' => 'Members of sensitive groups may experience health effects. The general public is less likely to be affected.',
                'alert' => false
            ];
        case 'Unhealthy':
            return [
                'aqi' => $aqi,
                'status' => 'Unhealthy',
                'level' => 4,
                'color' => '#dc2626', // Red
                'bg_color' => '#fee2e2',
                'border_color' => '#fca5a5',
                'health_advisory' => 'Some members of the general public may experience health effects; members of sensitive groups may experience more serious health effects.',
                'alert' => true
            ];
        case 'Very Unhealthy':
            return [
                'aqi' => $aqi,
                'status' => 'Very Unhealthy',
                'level' => 5,
                'color' => '#7c3aed', // Purple
                'bg_color' => '#f3e8ff',
                'border_color' => '#ddd6fe',
                'health_advisory' => 'Health alert: The risk of health effects is increased for everyone.',
                'alert' => true
            ];
        case 'Hazardous':
        default:
            return [
                'aqi' => $aqi,
                'status' => 'Hazardous',
                'level' => 6,
                'color' => '#881337', // Maroon
                'bg_color' => '#ffe4e6',
                'border_color' => '#fecdd3',
                'health_advisory' => 'Health warning of emergency conditions: everyone is more likely to be affected.',
                'alert' => true
            ];
    }
}
