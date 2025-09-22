<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../layout/header.php';
require_admin();

// Ürün ekleme işlemi
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image = null;

    // Resim yükleme
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../uploads/' . $image);
    }

    // Veritabanına ekle
    $sql_insert = "INSERT INTO products (name, description, price, stock, category_id, image, created_at)
                   VALUES (:name, :description, :price, :stock, :category_id, :image, NOW())";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':category_id' => $category_id,
        ':image' => $image
    ]);

    set_flash('Ürün başarıyla eklendi!');
    redirect('products.php');
    exit;
}

// --- Pagination ayarları ---
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam ürün sayısı
$total_stmt = $pdo->query("SELECT COUNT(*) FROM products");
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Ürünleri çekme sorgusu (limitli)
$sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Ürün Ekleme Formu -->
<h2>Yeni Ürün Ekle</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label>Ürün Adı:</label>
    <input type="text" name="name" required><br>

    <label>Açıklama:</label>
    <textarea name="description" required></textarea><br>

    <label>Fiyat:</label>
    <input type="number" step="0.01" name="price" required><br>

    <label>Stok:</label>
    <input type="number" name="stock" required><br>

    <label>Kategori:</label>
    <select name="category_id" required>
        <?php
        $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as $cat) {
            echo "<option value=\"{$cat['id']}\">" . htmlspecialchars($cat['name']) . "</option>";
        }
        ?>
    </select><br>

    <label>Resim:</label>
    <input type="file" name="image"><br>

    <button type="submit" name="add_product">Ekle</button>
</form>

<!-- Ürün Listesi Tablosu -->
<h2>Ürünler</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Ürün Adı</th>
        <th>Açıklama</th>
        <th>Fiyat</th>
        <th>Stok</th>
        <th>Kategori</th>
        <th>Resim</th>
        <th>İşlemler</th>
    </tr>
    <?php foreach ($products as $product): ?>
        <tr>
            <td><?= $product['id'] ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= htmlspecialchars($product['description']) ?></td>
            <td><?= $product['price'] ?></td>
            <td><?= $product['stock'] ?></td>
            <td><?= htmlspecialchars($product['category_name']) ?></td>
            <td>
                <?php if ($product['image']): ?>
                    <img src="../../uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="50">
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_product.php?id=<?= $product['id'] ?>">Düzenle</a>
                <a href="delete_product.php?id=<?= $product['id'] ?>" onclick="return confirm('Silmek istediğinizden emin misiniz?')">Sil</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Sayfalama Linkleri -->
<div style="margin-top:15px;">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="products.php?page=<?= $i ?>"
                style="margin:0 5px; <?= $i == $page ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../layout/footer.php';
?>