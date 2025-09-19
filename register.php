<?php
// Kayıt sayfası

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
        // Form verilerini al ve sanitize et
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Basit doğrulamalar
        if (!$username || !$email || !$password) {
            $message = "Lütfen tüm zorunlu alanları doldurun.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Geçersiz e-posta adresi.";
        } elseif ($password !== $confirm_password) {
            $message = "Şifreler eşleşmiyor.";
        } elseif (strlen($password) < 6) {
            $message = "Şifre en az 6 karakter olmalı.";
        } else {
            // Email daha önce kayıtlı mı kontrol et
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message = "Bu e-posta zaten kayıtlı.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                try {
                    $insert->execute([$username, $email, $hashed]);
                    set_flash("Kayıt başarılı! Giriş yapabilirsiniz.", "success");
                    redirect("login.php");
                } catch (PDOException $e) {
                    $message = "Kayıt sırasında bir hata oluştu.";
                }
            }
        }
    }
}

// Formu göster
$csrf_token = generate_csrf_token();
?>
<?php include "includes/header.php"; ?>

<h2>Kayıt Ol</h2>

<?php if ($flash): ?>
    <div class="flash <?php echo sanitize($flash['type']); ?>"><?php echo sanitize($flash['message']); ?></div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="error"><?php echo sanitize($message); ?></div>
<?php endif; ?>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <label>Kullanıcı Adı:</label><br>
    <input type="text" name="username" required value="<?php echo isset($username) ? sanitize($username) : ''; ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required value="<?php echo isset($email) ? sanitize($email) : ''; ?>"><br><br>

    <label>Şifre:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Şifre Tekrar:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <input type="submit" value="Kayıt Ol">
</form>

<?php include "includes/footer.php"; ?>