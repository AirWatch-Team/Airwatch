# AirWatch Database Setup Guide

This folder contains the complete SQL database initialization script for **AirWatch: Web-Based Air Quality Data Collection, Monitoring and Analysis System**.

## File Overview

* `airwatch.sql`: Database schema definition, table structure, indexes, and synthetic demo measurement records.

---

## How to Import in XAMPP / phpMyAdmin

1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open your browser and go to `http://localhost/phpmyadmin/`.
3. Click on the **Import** tab at the top.
4. Click **Choose File** and select `airwatch.sql` from this directory.
5. Scroll to the bottom and click **Import** (or **Go**).
6. phpMyAdmin will execute the script, create the `airwatch` database, set up the `air_quality_records` table with proper indexes, and populate 25 synthetic demo records.

---

## Command Line Import (MySQL CLI)

If you prefer using the MySQL command line interface:

```bash
mysql -u root -p < airwatch.sql
```

(Press Enter if there is no password set for root, which is default in XAMPP).

---

## Schema Information

* **Database Name**: `airwatch`
* **Table Name**: `air_quality_records`
* **Primary Key**: `id` (AUTO_INCREMENT)
* **Indexes**: `location`, `record_date`, `aqi`, `pollution_source`
