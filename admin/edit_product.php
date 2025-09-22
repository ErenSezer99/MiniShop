<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';
require_admin();

// Geçerli ürün ID kontrolü
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Geçersiz ürün ID");
}
$product_id = (int) $_GET['id'];

// Ürün bilgilerini çek
$sql = "SELECT * FROM products WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Ürün bulunamadı.");
}

// Güncelleme işlemi
if (isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image = $product['image'];

    // Yeni resim yüklendiyse değiştir
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImage = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $newImage);
        $image = $newImage;
    }

    // Veritabanını güncelle
    $sql_update = "UPDATE products 
                   SET name = :name, description = :description, price = :price, 
                       stock = :stock, category_id = :category_id, image = :image
                   WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':category_id' => $category_id,
        ':image' => $image,
        ':id' => $product_id
    ]);

    redirect("products.php");
    exit;
}

// Kategorileri çek
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Ürün Düzenle</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label>Ürün Adı:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br>

    <label>Açıklama:</label>
    <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea><br>

    <label>Fiyat:</label>
    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>

    <label>Stok:</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required><br>

    <label>Kategori:</label>
    <select name="category_id" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Mevcut Resim:</label>
    <?php if ($product['image']): ?>
        <img src="../uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="80"><br>
    <?php else: ?>
        <p>Resim yok</p>
    <?php endif; ?>

    <label>Yeni Resim (opsiyonel):</label>
    <input type="file" name="image"><br>

    <button type="submit" name="update_product">Güncelle</button>
</form>

<?php
include_once __DIR__ . '/footer.php';
?>