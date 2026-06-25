<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
session_destroy();

redirect('login.php', 'success', 'Vous êtes déconnecté.');
