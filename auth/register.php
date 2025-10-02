<?php
// Kayıt sayfası

include_once "../includes/functions.php";
include_once "../config/database.php";

$flash = get_flash();
$message = "";
$username = "";
$email = "";

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
            pg_prepare($dbconn, "check_email", "SELECT id FROM users WHERE email = $1");
            $res_check = pg_execute($dbconn, "check_email", [$email]);

            if (pg_fetch_assoc($res_check)) {
                $message = "Bu e-posta zaten kayıtlı.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                // insert
                pg_prepare($dbconn, "insert_user", "INSERT INTO users (username, email, password) VALUES ($1, $2, $3)");
                $res_insert = pg_execute($dbconn, "insert_user", [$username, $email, $hashed]);

                if ($res_insert) {
                    set_flash("Kayıt başarılı! Giriş yapabilirsiniz.", "success");
                    redirect("login.php");
                    exit;
                } else {
                    $message = "Kayıt sırasında bir hata oluştu.";
                }
            }
        }
    }
}

// Formu göster
$csrf_token = generate_csrf_token();
?>
<?php include "../includes/header.php"; ?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Kayıt Ol</h2>

    <?php if ($flash): ?>
        <div class="flash <?php echo sanitize($flash['type']); ?> mb-6">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo sanitize($flash['message']); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="mb-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo sanitize($message); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="" class="space-y-6" id="register-form">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div>
            <label for="username" class="form-label">Kullanıcı Adı:</label>
            <input
                type="text"
                id="username"
                name="username"
                required
                value="<?php echo $username; ?>"
                class="form-input"
                placeholder="Kullanıcı adınızı girin">
        </div>

        <div>
            <label for="email" class="form-label">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                value="<?php echo $email; ?>"
                class="form-input"
                placeholder="ornek@email.com">
        </div>

        <div>
            <label for="password" class="form-label">Şifre:</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="form-input"
                placeholder="••••••••">
        </div>

        <div>
            <label for="confirm_password" class="form-label">Şifre Tekrar:</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                class="form-input"
                placeholder="••••••••">
        </div>

        <div>
            <input
                type="submit"
                value="Kayıt Ol"
                class="form-button w-full"
                id="register-submit">
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-gray-600">
            Zaten hesabınız var mı?
            <a href="/MiniShop/auth/login.php" class="text-blue-600 hover:text-blue-800 font-medium">Giriş Yap</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>