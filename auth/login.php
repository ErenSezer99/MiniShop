<?php
// Giriş sayfası

include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/database.php";

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
            pg_prepare($dbconn, "get_user_by_email", "SELECT * FROM users WHERE email = $1");
            $res = pg_execute($dbconn, "get_user_by_email", [$email]);
            $user = pg_fetch_assoc($res);

            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'user'
                ];

                set_flash("Başarıyla giriş yapıldı.", "success");
                redirect('/MiniShop/products/index.php');
                exit;
            } else {
                $message = "Email veya şifre hatalı.";
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<?php include "../includes/header.php"; ?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Giriş Yap</h2>

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

    <form method="post" action="" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div>
            <label for="email" class="form-label">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                value="<?php echo isset($email) ? sanitize($email) : ''; ?>"
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
            <input
                type="submit"
                value="Giriş Yap"
                class="form-button w-full">
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-gray-600">
            Hesabınız yok mu?
            <a href="/MiniShop/auth/register.php" class="text-blue-600 hover:text-blue-800 font-medium">Kayıt Ol</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>