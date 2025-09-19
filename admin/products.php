<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';
require_admin();

// Ürünleri çekme sorgusu
$sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
                    <img src="../uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="50">
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_product.php?id=<?= $product['id'] ?>">Düzenle</a> |
                <a href="delete_product.php?id=<?= $product['id'] ?>" onclick="return confirm('Silmek istediğinizden emin misiniz?')">Sil</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php
// Footer
include_once __DIR__ . '/footer.php';
?>