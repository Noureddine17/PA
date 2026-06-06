<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

if (!isset($_SESSION['id_user'])) {
    redirect('../auth/login.php', 'error', 'Vous devez vous connecter.');
}

$stmt = $pdo->prepare('SELECT role FROM UTILISATEUR WHERE id_user = ?');
$stmt->execute([$_SESSION['id_user']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    redirect('../auth/login.php', 'error', 'Utilisateur introuvable.');
}

$_SESSION['role'] = $currentUser['role'];

if ($currentUser['role'] !== 'admin') {
    redirect('client.php', 'error', 'Accès réservé aux administrateurs.');
}

$userId = (int) ($_GET['id'] ?? 0);

if ($userId <= 0) {
    redirect('admin.php', 'error', 'Utilisateur introuvable.');
}

$stmt = $pdo->prepare('
    SELECT u.id_user, u.nom, u.prenom, u.email, u.role, n.id_newsletter AS newsletter
    FROM UTILISATEUR u
    LEFT JOIN NEWSLETTER n ON n.id_user = u.id_user
    WHERE u.id_user = ?
');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect('admin.php', 'error', 'Utilisateur introuvable.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'client';
    $newsletter = isset($_POST['newsletter']);

    if ($nom === '' || $prenom === '' || $email === '') {
        redirect('update_user.php?id=' . $userId, 'error', 'Tous les champs sont obligatoires.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('update_user.php?id=' . $userId, 'error', 'Email invalide.');
    }

    if (!in_array($role, ['client', 'admin', 'expert'])) {
        redirect('update_user.php?id=' . $userId, 'error', 'Role invalide.');
    }

    $stmt = $pdo->prepare('SELECT id_user FROM UTILISATEUR WHERE email = ? AND id_user != ?');
    $stmt->execute([$email, $userId]);
    $emailExiste = $stmt->fetch();

    if ($emailExiste) {
        redirect('update_user.php?id=' . $userId, 'error', 'Cet email est déjà utilisé.');
    }

    $stmt = $pdo->prepare('
        UPDATE UTILISATEUR
        SET nom = ?, prenom = ?, email = ?, role = ?
        WHERE id_user = ?
    ');
    $stmt->execute([$nom, $prenom, $email, $role, $userId]);

    if ($newsletter) {
        $stmt = $pdo->prepare('SELECT id_newsletter FROM NEWSLETTER WHERE id_user = ?');
        $stmt->execute([$userId]);
        $newsletterExiste = $stmt->fetch();

        if ($newsletterExiste) {
            $stmt = $pdo->prepare('UPDATE NEWSLETTER SET email_inscrit = ? WHERE id_user = ?');
            $stmt->execute([$email, $userId]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO NEWSLETTER (email_inscrit, id_user) VALUES (?, ?)');
            $stmt->execute([$email, $userId]);
        }
    } else {
        $stmt = $pdo->prepare('DELETE FROM NEWSLETTER WHERE id_user = ?');
        $stmt->execute([$userId]);
    }

    if ($userId === (int) $_SESSION['id_user']) {
        $_SESSION['email'] = $email;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $prenom;
        $_SESSION['role'] = $role;
    }

    redirect('admin.php', 'success', 'Utilisateur modifié.');
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto max-w-3xl">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Modifier un utilisateur</h1>

            <form action="update_user.php?id=<?= htmlspecialchars($user['id_user']) ?>" method="post"
                class="mt-8 space-y-5">
                <div>
                    <label for="prenom" class="font-hatton text-main">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>"
                        class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                </div>

                <div>
                    <label for="nom" class="font-hatton text-main">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>"
                        class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                </div>

                <div>
                    <label for="email" class="font-hatton text-main">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                        class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                </div>

                <div>
                    <label for="role" class="font-hatton text-main">Role</label>
                    <select id="role" name="role"
                        class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                        <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>client</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                        <option value="expert" <?= $user['role'] === 'expert' ? 'selected' : '' ?>>expert</option>

                    </select>
                </div>

                <label class="flex items-center gap-3 font-hatton text-main">
                    <input type="checkbox" name="newsletter" value="1" <?= $user['newsletter'] ? 'checked' : '' ?>>
                    Inscrit à la newsletter
                </label>

                <div class="flex flex-wrap gap-3 pt-3">
                    <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton text-main">
                        Enregistrer
                    </button>
                    <a href="admin.php" class="rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                        Retour
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>