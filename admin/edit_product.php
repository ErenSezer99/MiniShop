<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';
require_admin();

if (!isset($_GET['id'])) {
    redirect('products.php');
    exit;
}

$product_id = (int)$_GET['id'];

// Ürünü çek
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    set_flash('Ürün bulunamadı!', 'error');
    redirect('products.php');
    exit;
}

// Ürün güncelleme işlemi
if (isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image = $product['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $image);

        // Önceki resmi sil
        if (!empty($product['image'])) {
            $old_image = __DIR__ . '/../uploads/' . $product['image'];
            if (file_exists($old_image)) unlink($old_image);
        }
    }

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

    set_flash('Ürün başarıyla güncellendi!');
    redirect('products.php');
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

    <label>Resim:</label>
    <input type="file" name="image"><br>
    <?php if (!empty($product['image'])): ?>
        <img src="../uploads/<?= $product['image'] ?>" width="80" alt="Ürün Resmi">
    <?php endif; ?>

    <button type="submit" name="update_product">Güncelle</button>
</form>

<?php include_once __DIR__ . '/footer.php'; ?>