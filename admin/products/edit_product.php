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

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Ürün Düzenle</h2>
    <p class="text-gray-600">Ürün bilgilerini güncelleyebilirsiniz.</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <form action="" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="form-label">Ürün Adı:</label>
            <input type="text" id="name" name="name" value="<?= sanitize($product['name']) ?>" required class="form-input">
        </div>

        <div>
            <label for="price" class="form-label">Fiyat:</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= $product['price'] ?>" required class="form-input">
        </div>

        <div>
            <label for="stock" class="form-label">Stok:</label>
            <input type="number" id="stock" name="stock" value="<?= $product['stock'] ?>" required class="form-input">
        </div>

        <div>
            <label for="category_id" class="form-label">Kategori:</label>
            <select id="category_id" name="category_id" required class="form-input">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= sanitize($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="description" class="form-label">Açıklama:</label>
            <textarea id="description" name="description" required class="form-input" rows="3"><?= sanitize($product['description']) ?></textarea>
        </div>

        <div class="md:col-span-2">
            <label for="image" class="form-label">Resim:</label>
            <input type="file" id="image" name="image" class="form-input">

            <?php if (!empty($product['image'])): ?>
                <div class="mt-4">
                    <p class="text-gray-700 mb-2">Mevcut Resim:</p>
                    <img src="../../uploads/<?= $product['image'] ?>" alt="Ürün Resmi" class="w-32 h-32 object-cover rounded">
                </div>
            <?php endif; ?>
        </div>

        <div class="md:col-span-2">
            <button type="submit" name="update_product" class="form-button">
                <i class="fas fa-save mr-2"></i> Ürünü Güncelle
            </button>
            <a href="products.php" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Geri
            </a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>