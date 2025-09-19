<?php
// Giriş sayfası

session_start();
include "config/database.php";
include "includes/functions.php";

$flash = get_flash();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Geçersiz istek (CSRF doğrulaması başarısız).";
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $message = "Lütfen e-posta ve şifre girin.";
        } else {
            // Kullanıcıyı al
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';

                set_flash("Başarıyla giriş yapıldı.", "success");
                redirect("index.php");
            } else {
                $message = "Email veya şifre hatalı.";
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<?php include "includes/header.php"; ?>

<h2>Giriş Yap</h2>

<?php if ($flash): ?>
    <div class="flash <?php echo sanitize($flash['type']); ?>"><?php echo sanitize($flash['message']); ?></div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="error"><?php echo sanitize($message); ?></div>
<?php endif; ?>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <label>Email:</label><br>
    <input type="email" name="email" required value="<?php echo isset($email) ? sanitize($email) : ''; ?>"><br><br>

    <label>Şifre:</label><br>
    <input type="password" name="password" required><br><br>

    <input type="submit" value="Giriş Yap">
</form>

<?php include "includes/footer.php"; ?>