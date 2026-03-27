-- ============================================
--  TypeForge — Database Schema
--  Run this ONCE in phpMyAdmin > SQL tab
-- ============================================

CREATE DATABASE IF NOT EXISTS typing_app;
USE typing_app;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scores table
CREATE TABLE IF NOT EXISTS scores (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT            NOT NULL,
    wpm        INT            NOT NULL,
    accuracy   DECIMAL(5,2)  NOT NULL,
    mode       VARCHAR(20)   DEFAULT 'words',
    duration   INT           DEFAULT 60,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Texts table (for sentence / paragraph modes)
CREATE TABLE IF NOT EXISTS texts (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    type    ENUM('sentence', 'paragraph') NOT NULL
);

-- ============================================
--  Seed Data — Sentences
-- ============================================
INSERT INTO texts (content, type) VALUES
('The quick brown fox jumps over the lazy dog near the riverbank at dawn.', 'sentence'),
('She sells seashells by the seashore where the waves crash endlessly.', 'sentence'),
('Programming is the art of telling a computer exactly what you want it to do.', 'sentence'),
('Technology has transformed the way we communicate and interact with the world.', 'sentence'),
('Every great developer you know started as a beginner who refused to give up.', 'sentence'),
('Speed comes from confidence and confidence comes from muscle memory built over time.', 'sentence'),
('A good programmer writes code that humans can read, not just machines.', 'sentence'),

-- ============================================
--  Seed Data — Paragraphs
-- ============================================
('The sun dipped below the horizon, painting the sky in brilliant shades of orange and crimson. Birds returned to their nests as darkness slowly crept across the landscape. The cool evening breeze carried the scent of pine and earth, a reminder that nature continued its eternal cycle regardless of human concerns.', 'paragraph'),
('Learning to type quickly is a valuable skill in the modern world. With practice and dedication, most people can significantly improve their typing speed and accuracy. The key is to focus on proper finger placement and to build muscle memory through consistent repetition. Over time, typing becomes automatic.', 'paragraph'),
('A database is an organized collection of structured information stored electronically. Databases are managed by systems which provide tools to store, modify, and extract information. SQL stands for Structured Query Language, the standard used to communicate with relational databases. It is a fundamental skill for any backend developer.', 'paragraph');