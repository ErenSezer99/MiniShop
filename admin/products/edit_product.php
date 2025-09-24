<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../includes/header.php';
require_admin();

if (!isset($_GET['id'])) {
    redirect('products.php');
    exit;
}

$product_id = (int)$_GET['id'];

// Ürünü çek
pg_prepare($dbconn, "select_product", "SELECT * FROM products WHERE id = $1");
$res = pg_execute($dbconn, "select_product", [$product_id]);
$product = pg_fetch_assoc($res);

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
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../uploads/' . $image);

        // Önceki resmi sil
        if (!empty($product['image'])) {
            $old_image = __DIR__ . '/../../uploads/' . $product['image'];
            if (file_exists($old_image)) unlink($old_image);
        }
    }

    // Ürün güncelleme
    pg_prepare($dbconn, "update_product", "
        UPDATE products
        SET name=$1, description=$2, price=$3, stock=$4, category_id=$5, image=$6
        WHERE id=$7
    ");
    pg_execute($dbconn, "update_product", [$name, $description, $price, $stock, $category_id, $image, $product_id]);

    set_flash('Ürün başarıyla güncellendi!');
    redirect('products.php');
    exit;
}

// Kategorileri çek
pg_prepare($dbconn, "select_categories", "SELECT id, name FROM categories ORDER BY name ASC");
$res_categories = pg_execute($dbconn, "select_categories", []);
$categories = [];
while ($row = pg_fetch_assoc($res_categories)) {
    $categories[] = $row;
}
?>

<h2>Ürün Düzenle</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label>Ürün Adı:</label>
    <input type="text" name="name" value="<?= sanitize($product['name']) ?>" required><br>

    <label>Açıklama:</label>
    <textarea name="description" required><?= sanitize($product['description']) ?></textarea><br>

    <label>Fiyat:</label>
    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>

    <label>Stok:</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required><br>

    <label>Kategori:</label>
    <select name="category_id" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                <?= sanitize($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Resim:</label>
    <input type="file" name="image"><br>
    <?php if (!empty($product['image'])): ?>
        <img src="../../uploads/<?= $product['image'] ?>" width="80" alt="Ürün Resmi">
    <?php endif; ?>

    <button type="submit" name="update_product">Güncelle</button>
</form>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>