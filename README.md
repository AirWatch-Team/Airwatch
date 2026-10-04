# AIRWATCH 🌍
## Web-Based Air Quality Data Collection, Monitoring and Analysis System

AirWatch is a complete, production-grade full-stack environmental monitoring platform built using **HTML5, CSS3, Vanilla JavaScript, Chart.js, PHP 8+ (PDO), and MySQL**.

The application allows environmental researchers, municipal personnel, and citizens to collect, monitor, analyze, report, and manage air quality index (AQI) measurements across diverse geographic stations.

---

## 🌿 Key Features

1. **Full CRUD Operations**:
   * **Create**: Submit air quality observations with client & server-side validation.
   * **Read**: Retrieve measurement records with search, filtering, and pagination.
   * **Update**: Edit existing database records through interactive modal forms.
   * **Delete**: Safely delete records with confirmation prompts.

2. **Real-Time Data Visualization**:
   * **AQI vs Date**: Line chart tracking historical air quality index trends.
   * **Average AQI by Location**: Bar chart comparing mean pollution levels across zones.
   * **PM2.5 Trend**: Line chart tracking fine particulate matter.
   * **Pollution Sources**: Doughnut chart showing distribution percentages of emission sources.

3. **High-Performance Backend Aggregations**:
   * All metrics (Average AQI, Peak AQI, PM2.5/PM10 averages, unique location counts) are calculated directly inside MySQL using optimized `GROUP BY` and SQL aggregation queries.

4. **Automated Environmental Insights**:
   * Autonomously generates data-driven readable insights highlighting pollution hotspots, cleanest locations, and dominant emission sources.

5. **Side-by-Side Location Comparison Tool**:
   * Select any two monitored locations to compare AQI, particulate concentrations, and record counts side-by-side with comparison tables and bar charts.

6. **Reporting & Data Export**:
   * **Printable Environmental Report**: Print-friendly layout with custom `@media print` styling (`window.print()`).
   * **CSV Export**: Direct stream download of database records for Excel/data science processing.

7. **Synthetic Demo Data System**:
   * One-click seed function (`seed.php`) to insert 25 synthetic measurement records across 5 distinct zones for instant evaluation.
   * Clear database function (`clear.php`) to reset records.

8. **Light Environmental Design System**:
   * Built specifically with a fresh, modern Nature/Sky/Clean Air aesthetic (White, Soft Green, Mint, Sky Blue, Charcoal text) avoiding dark cyberpunk themes.

---

## 🛠️ Technology Stack

* **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+), Chart.js
* **Backend**: PHP 8+, REST-style JSON API endpoints, PDO (PHP Data Objects)
* **Database**: MySQL 5.7+ / MySQL 8.0+ / MariaDB
* **Web Server Compatibility**: Apache (XAMPP / WAMP / Linux LAMP)

---

## 📁 Project Structure

```text
AirWatch/
├── frontend/
│   ├── index.html            # Home page & system introduction
│   ├── collect.html          # Data collection form & real-time preview
│   ├── dashboard.html        # Interactive monitoring dashboard & charts
│   ├── analysis.html         # Hotspot analysis & location comparison
│   ├── locations.html        # Monitored locations grid & GIS placeholder
│   ├── about.html            # System architecture & documentation
│   ├── css/
│   │   └── style.css         # Environmental light design system stylesheet
│   └── js/
│       ├── api.js            # Standardized API client module
│       ├── script.js         # Global navigation & alert banner system
│       ├── dashboard.js      # Dashboard stats, table & Chart.js logic
│       ├── analysis.js       # Analysis metrics, comparison & insights
│       └── report.js         # Printable report generator & CSV trigger
│
├── backend/
│   ├── config/
│   │   └── database.php      # Central PDO database connection & CORS
│   ├── api/
│   │   ├── records/
│   │   │   ├── create.php    # POST: Insert new record
│   │   │   ├── read.php      # GET: List/filter/paginate records
│   │   │   ├── update.php    # POST/PUT: Update record
│   │   │   └── delete.php    # POST/DELETE: Delete record
│   │   ├── dashboard/
│   │   │   └── statistics.php # GET: SQL aggregate dashboard metrics
│   │   ├── analysis/
│   │   │   ├── locations.php  # GET: Location-wise aggregate stats
│   │   │   └── pollution-sources.php # GET: Source percentages
│   │   ├── reports/
│   │   │   └── generate.php  # GET: Report data aggregation
│   │   ├── demo/
│   │   │   ├── seed.php      # POST: Seed synthetic demo records
│   │   │   └── clear.php     # POST: Truncate database table
│   │   └── export/
│   │       └── csv.php       # GET: Stream CSV file download
│   └── helpers/
│       ├── response.php      # Standard JSON response helper
│       ├── validation.php    # Input validation & sanitization
│       └── aqi.php           # US EPA AQI classification engine
│
├── database/
│   ├── airwatch.sql          # Complete SQL schema & 25 demo records
│   └── README.md             # Database import instructions
│
├── README.md                 # Complete project documentation
└── AirWatch.zip              # Bundled distribution package
```

---

## 💻 XAMPP Setup & How to Run

### Step 1: Place Files in XAMPP
Copy the entire `AirWatch` folder into your XAMPP `htdocs` directory:
```text
C:\xampp\htdocs\AirWatch
```

### Step 2: Start Apache and MySQL
Open the **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

### Step 3: Import Database
1. Open your browser and navigate to `http://localhost/phpmyadmin/`.
2. Click **Import** tab.
3. Choose file `C:\xampp\htdocs\AirWatch\database\airwatch.sql`.
4. Click **Go** / **Import**. phpMyAdmin will create the `airwatch` database and populate 25 synthetic demo records.

### Step 4: Open Application URL
Access AirWatch in your browser at:
```text
http://localhost/AirWatch/frontend/index.html
```
*(Do NOT open HTML files directly via `file://` protocols, as PHP APIs require the Apache server).*

---

## 🌐 API Documentation

All API endpoints return JSON with standard HTTP status codes (`200`, `201`, `400`, `404`, `500`).

| Method | Endpoint | Description | Request Parameters / Payload |
| :--- | :--- | :--- | :--- |
| `POST` | `/backend/api/records/create.php` | Insert new record | `location`, `date`, `time`, `aqi`, `pm25`, `pm10`, `temperature`, `humidity`, `pollution_source`, `notes` |
| `GET` | `/backend/api/records/read.php` | Read/Filter records | `id` (optional), `location`, `start_date`, `end_date`, `status`, `search`, `page`, `limit` |
| `POST` | `/backend/api/records/update.php` | Update record | `id` (required), `location`, `date`, `aqi`, `pm25`, `pm10`, etc. |
| `POST` | `/backend/api/records/delete.php` | Delete record | `id` (required) |
| `GET` | `/backend/api/dashboard/statistics.php` | Dashboard stats | None |
| `GET` | `/backend/api/analysis/locations.php` | Location aggregate stats | None |
| `GET` | `/backend/api/analysis/pollution-sources.php` | Pollution source percentages | None |
| `GET` | `/backend/api/reports/generate.php` | Environmental report data | None |
| `POST` | `/backend/api/demo/seed.php` | Seed 25 demo records | None |
| `POST` | `/backend/api/demo/clear.php` | Clear database table | None |
| `GET` | `/backend/api/export/csv.php` | Download CSV export | `location`, `start_date`, `end_date` (optional filters) |

### Sample Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Record retrieved successfully.",
  "data": {
    "id": 1,
    "location": "Main Road",
    "record_date": "2026-10-01",
    "record_time": "08:30:00",
    "aqi": 165,
    "aqi_status": "Unhealthy",
    "pm25": 84.50,
    "pm10": 142.10,
    "temperature": 28.5,
    "humidity": 62.00,
    "pollution_source": "Vehicle Traffic",
    "notes": "Heavy morning rush hour traffic.",
    "aqi_details": {
      "status": "Unhealthy",
      "level": 4,
      "color": "#dc2626",
      "health_advisory": "Some members of the general public may experience health effects."
    }
  }
}
```

---

## 📏 Documented AQI Standard (US EPA)

AirWatch implements the standardized US EPA AQI classification across backend APIs and frontend UI badges:

* **0 – 50**: Good (`#10b981` Green)
* **51 – 100**: Moderate (`#d97706` Amber)
* **101 – 150**: Unhealthy for Sensitive Groups (`#ea580c` Orange)
* **151 – 200**: Unhealthy (`#dc2626` Red)
* **201 – 300**: Very Unhealthy (`#7c3aed` Purple)
* **301+**: Hazardous (`#881337` Maroon)

---

## 🔒 Security Implementation

1. **PDO Prepared Statements**: All database operations bind parameters safely to eliminate SQL Injection risks.
2. **Server-Side Validation**: PHP validates required fields, numeric constraints, humidity boundaries (0-100%), and date formats independently of frontend checks.
3. **Output Escaping**: Data rendered to the HTML DOM is escaped using `htmlspecialchars()` to prevent Cross-Site Scripting (XSS).
4. **Controlled Error Responses**: Database passwords, file paths, and SQL query strings are masked behind user-friendly JSON error messages.

---

## 🔑 Authentication-Ready Architecture

AirWatch is designed for future extension with Role-Based Access Control (RBAC). Planned expansion tables include:
* `users`: `id`, `username`, `password_hash`, `email`, `role_id`, `created_at`
* `roles`: `id`, `role_name` (`Admin`, `Data Collector`, `Public Viewer`)

---

## ⚠️ Real-World Data Disclaimer

AirWatch is a web-based data collection, monitoring, and analysis software application. Unless connected directly to calibrated hardware sensors or verified environmental telemetry APIs, records stored within the system represent manual user entries, imported datasets, or synthetic demo values.

---

## 🚀 Future Roadmap

* **Hardware IoT Sensor Integration**: Connecting ESP32 / Arduino / MQ-135 / PMS5003 sensors over Wi-Fi / MQTT.
* **Spatial Leaflet Maps**: Rendering interactive OpenStreetMap heatmap layers for pollution distribution.
* **Alert Notifications**: Automated SMS (Twilio) and SMTP email alerts when AQI exceeds emergency thresholds.
