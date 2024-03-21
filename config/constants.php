<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename constants.php
 */

const DB_HOST = "postgres-db";
const DB_USER = "admin";
const DB_PASS = "password";
const DB_NAME = "INFT2100-F2023";
const DB_PORT = "5432";
const COOKIE_LIFESPAN = "2592000"; // 1 month: 60x60x24x30
const ADMIN = 's';
const AGENT = 'a';
const CLIENT = 'c';
const PENDING = 'p';
const DISABLED = 'd';

// Alert types
const ALERT_PRIMARY = 'alert alert-primary';
const ALERT_SECONDARY = 'alert alert-secondary';
const ALERT_SUCCESS = 'alert alert-success';
const ALERT_DANGER = 'alert alert-danger';
const ALERT_WARNING = 'alert alert-warning';
const ALERT_INFO = 'alert alert-info';
const ALERT_LIGHT = 'alert alert-light';
const ALERT_DARK = 'alert alert-dark';