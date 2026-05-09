<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
session_unset();

redirect('login.php', 'success', 'Vous êtes déconnecté.');
