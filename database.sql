CREATE DATABASE IF NOT EXISTS atp_manager;
USE atp_manager;

-- Table 1: users
-- Stores all player accounts
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,  -- Unique ID for each user
    full_name   VARCHAR(100) NOT NULL,           -- Player's full name
    email       VARCHAR(150) NOT NULL UNIQUE,    -- Email used to log in (must be unique)
    password    VARCHAR(255) NOT NULL,           -- Hashed password (never plain text!)
    country     VARCHAR(100) DEFAULT NULL,       -- Player's country
    ranking     INT DEFAULT NULL,                -- Current ATP ranking number (NULL if unranked)
    role        ENUM('player', 'admin') DEFAULT 'player', -- Role: player or admin
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP       -- When the account was created
);

-- Table 2: tournaments
-- Stores all ATP tournaments in the system
CREATE TABLE tournaments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,         -- Tournament name 
    location        VARCHAR(150) NOT NULL,          -- City, Country
    surface         ENUM('Hard', 'Clay', 'Grass', 'Indoor Hard') NOT NULL, -- Court surface type
    category        ENUM('Grand Slam', 'ATP Masters 1000', 'ATP 500', 'ATP 250') NOT NULL, -- Tournament tier
    ranking_points  INT NOT NULL,                  -- Points awarded to the winner
    total_slots     INT NOT NULL DEFAULT 32,       -- Maximum number of players allowed
    start_date      DATE NOT NULL,                 -- Tournament start date
    end_date        DATE NOT NULL,                 -- Tournament end date
    registration_deadline DATE NOT NULL,           -- Last day to register
    status          ENUM('Upcoming', 'Open', 'Closed', 'Ongoing', 'Completed') DEFAULT 'Upcoming',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 3: registrations
-- Tracks which players registered for which tournaments
CREATE TABLE registrations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,                  -- Which player registered
    tournament_id   INT NOT NULL,                  -- Which tournament they registered for
    registered_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When they registered
    status          ENUM('Confirmed', 'Withdrawn', 'Pending') DEFAULT 'Confirmed',

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,

    UNIQUE KEY unique_registration (user_id, tournament_id)
);

-- TABLE 4: ranking_history
-- Tracks how a player's ranking changes over time
CREATE TABLE ranking_history (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,                      -- Which player
    ranking     INT NOT NULL,                      -- Their ranking at this point in time
    points      INT NOT NULL DEFAULT 0,            -- Total ATP points at this time
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
