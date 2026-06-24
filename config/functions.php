<?php
function redirect($url, $type = '', $message = '')
{
    if ($message !== '') {
        $_SESSION['alert'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    header('Location: ' . $url);
    exit;
}

function getAlert()
{
    $alert = $_SESSION['alert'] ?? null;
    unset($_SESSION['alert']);

    return $alert;
}

function displayAlert()
{
    $alert = getAlert();
    if ($alert) {
        $bgColor = $alert['type'] === 'success' ? 'bg-[#DDEEDC]' : 'bg-red-100';
        $textColor = $alert['type'] === 'success' ? 'text-main' : 'text-red-700';
        echo '<div class="mb-6 rounded-[22px] ' . $bgColor . ' px-4 py-3 text-center font-hatton ' . $textColor . ' sm:rounded-full sm:px-5">';
        echo htmlspecialchars($alert['message']);
        echo '</div>';
    }
}
function requireLogin($loginUrl = '../auth/login.php')
{
    if (!isset($_SESSION['id_user'])) {
        redirect($loginUrl, 'error', 'Vous devez vous connecter.');
    }
}

function getCurrentRole($pdo)
{
    if (!isset($_SESSION['id_user'])) {
        redirect('../auth/login.php', 'error', 'Vous devez vous connecter.');
    }

    if (!empty($_SESSION['role'])) {
        return $_SESSION['role'];
    }

    $stmt = $pdo->prepare('SELECT role FROM UTILISATEUR WHERE id_user = ?');
    $stmt->execute([$_SESSION['id_user']]);
    $user = $stmt->fetch();

    if (!$user) {
        redirect('../auth/login.php', 'error', 'Utilisateur introuvable.');
    }

    $_SESSION['role'] = $user['role'];
    return $user['role'];
}

function requireRole($pdo, $roles, $loginUrl = '../auth/login.php', $fallbackUrl = 'client.php', $message = 'Accès non autorisé.')
{
    requireLogin($loginUrl);

    $roles = (array) $roles;
    $role = getCurrentRole($pdo);

    if ($role === null) {
        redirect($loginUrl, 'error', 'Utilisateur introuvable.');
    }

    if (!in_array($role, $roles, true)) {
        redirect($fallbackUrl, 'error', $message);
    }

    return $role;
}

function isCurrentAdmin($pdo)
{
    return getCurrentRole($pdo) === 'admin';
}

function sendMail($to, $subject, $message)
{
    $from = 'marocato80@gmail.com';

    $headers = "From: KAESKIN <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}


function logLoginAttempt(bool $isSuccess, string $email): void
{
    $status = $isSuccess ? 'réussie' : 'échouée';
    $line = date("Y-m-d H:i:s") . " - Tentative de connexion $status de : " . $email . PHP_EOL;
    file_put_contents(__DIR__ . '/../log.txt', $line, FILE_APPEND);
}
