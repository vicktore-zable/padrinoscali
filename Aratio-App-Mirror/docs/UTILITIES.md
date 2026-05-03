# Aratio Project Utilities Guide

This document serves as a reference for the various utility and maintenance scripts used in the Aratio Multi-Campaign Management System. These scripts are organized within the `scripts/` directory.

## 📂 Directory Structure

The `scripts/` directory is organized into four main categories:

- `scripts/deploy/`: Scripts for deploying files to the remote server via FTP.
- `scripts/diagnostics/`: Tools for checking the health and configuration of the environment.
- `scripts/maintenance/`: Scripts for database fixes, cache management, and system updates.
- `scripts/tools/`: General utility tools for reading logs, listing files, and data exploration.

---

## 🚀 Deployment Scripts (`scripts/deploy/`)

These scripts automate the process of uploading files to the Hostinger server.

| Script Name | Purpose |
| :--- | :--- |
| `subir-a-hostinger.ps1` | General script to upload files to Hostinger. |
| `subir_core.ps1` | Uploads the core controller and router files for `mod_lider`. |
| `subir_portal_lider.ps1` | Uploads the complete Portal Leader module. |
| `subir_views.ps1` | Uploads scripts to update remote database views. |
| `deploy_phpmailer.php` | Utility to set up or verify PHPMailer on the server. |

**Usage:** Execute the `.ps1` files from a PowerShell terminal.

---

## 🔍 Diagnostics (`scripts/diagnostics/`)

Tools to verify the system's state and troubleshoot issues.

| Script Name | Purpose |
| :--- | :--- |
| `verificar_entorno_local.php` | Checks PHP version, DB connection, and mandatory table columns. |
| `diag_portal.php` | Detailed diagnostic for the user portal and its dependencies. |
| `diag_sess.php` | Checks session configuration and unified session naming. |
| `db_diag.php` | Basic database connection and schema check. |

**Usage:** Access via a web browser (e.g., `http://localhost/scripts/diagnostics/verificar_entorno_local.php`) or PHP CLI.

---

## 🛠️ Maintenance (`scripts/maintenance/`)

Actions that modify the system state or perform cleanup.

| Script Name | Purpose |
| :--- | :--- |
| `run_fix_views.php` | Creates or replaces critical SQL views needed for dashboards. |
| `reset_opcache.php` | Clears the PHP OPcache to force file reload on the server. |
| `clear_cache.php` | Deletes local/cached temporary files. |
| `fix_database_schema.php` | Applies fixes to the database schema structure. |

---

## 🔧 Tools & Data (`scripts/tools/`)

Utility scripts for reporting and exploration.

| Script Name | Purpose |
| :--- | :--- |
| `read_error_log.php` | Displays the last few lines of the PHP error log. |
| `list_fields.php` | Lists all columns for a specific table in the database. |
| `get_test_user.php` | Displays sample user credentials for testing logins. |
| `summarize_hierarchies.php` | Generates a report on leader-follower relationships. |

---

> [!NOTE]
> All PHP scripts in these directories have been updated with relative paths to correctly include `config/config.php` from their new locations.
