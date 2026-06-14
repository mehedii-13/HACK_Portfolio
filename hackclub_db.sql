-- ═══════════════════════════════════════════════════════════════
-- HACK Club KUET — Database Schema
-- Import via phpMyAdmin OR run: mysql -u root < hackclub_db.sql
-- Then run /hack/setup.php?key=setup2025 to seed the admin account
-- ═══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS hackclub_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE hackclub_db;

-- ── Admin (single account, seeded by setup.php) ───────────────
CREATE TABLE IF NOT EXISTS admins (
    id            INT           AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)   NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Events ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    type        ENUM('meeting','class','workshop','project_showcase','competition') NOT NULL DEFAULT 'meeting',
    event_date  DATE         NOT NULL,
    event_time  TIME         NULL,
    venue       VARCHAR(200) NULL,
    description TEXT         NULL,
    image       VARCHAR(255) NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Members (self-registered on public site) ──────────────────
CREATE TABLE IF NOT EXISTS members (
    id            INT          AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    department    VARCHAR(100) NULL,
    batch         VARCHAR(20)  NULL,
    student_id    VARCHAR(30)  NULL,
    phone         VARCHAR(20)  NULL,
    status        ENUM('pending','approved','rejected') DEFAULT 'pending',
    registered_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Upcoming Competitions (external, admin-managed) ───────────
CREATE TABLE IF NOT EXISTS competitions (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    organizer   VARCHAR(200) NULL,
    description TEXT         NULL,
    deadline    DATE         NULL,
    event_url   VARCHAR(500) NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
