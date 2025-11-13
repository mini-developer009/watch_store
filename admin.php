<?php
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}
$is_super = $_SESSION['is_super'] ?? false;
$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন ড্যাশবোর্ড</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Hind Siliguri', sans-serif; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .main-content { margin-left: 230px; padding: 20px; }
        .navbar { z-index: 101; }
        .note-input { width: 100%; border: 1px dashed #ccc; padding: 5px; background-color: #fefde8; }
        .note-input:focus { background-color: #fff; border: 1px solid #888; }
        .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        
        #orders-table .action-cell {
            min-width: 200px; /* ড্রপডাউন ও বাটনকে পাশাপাশি রাখার জন্য */
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        /* নতুন পেজের টাইটেল লাল করার জন্য */
        label[for="new_page_title"] {
            color: red;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">অ্যাডমিন প্যানেল</a>
        <div class="navbar-nav"><div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="logout.php">স্বাগতম, <?php echo $admin_name; ?> (লগ আউট)</a>
        </div></div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column nav-pills" id="admin-tabs" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active w-100 text-start" data-bs-toggle="pill" data-bs-target="#orders-panel" type="button"><i class="fas fa-shopping-cart fa-fw me-2"></i> অর্ডার ম্যানেজ</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#manage-products-panel" type="button"><i class="fas fa-list fa-fw me-2"></i> প্রোডাক্ট ম্যানেজ</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#add-product-panel" type="button"><i class="fas fa-plus-circle fa-fw me-2"></i> নতুন প্রোডাক্ট যোগ</button></li>
                        
                        <li class="nav-item" role="presentation"><button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#create-page-panel" type="button"><i class="fas fa-file-alt fa-fw me-2"></i> নতুন ল্যান্ডিং পেজ</button></li>

                        <?php if ($is_super): ?><li class="nav-item" role="presentation"><button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#admins-panel" type="button"><i class="fas fa-user-shield fa-fw me-2"></i> সাব-অ্যাডমিন ম্যানেজ</button></li><?php endif; ?>
                    </ul>
                </div>
            </nav>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="tab-content" id="admin-tabs-content">
                    
                    <div class="tab-pane fade show active" id="orders-panel" role="tabpanel">
                        <h2 class="mt-3">অর্ডার ম্যানেজ করুন</h2>
                        <div id="order-update-alert" class="alert d-none" role="alert"></div>
                        <div class="card"><div class="card-body"><div class="table-responsive">
                            <table id="orders-table" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>আইডি</th> <th>স্ট্যাটাস</th> <th>ভেরিফিকেশন</th> <th>ক্রেতা</th> <th>ফোন</th>
                                        <th>ঠিকানা</th> <th>প্রোডাক্ট</th> <th>নোট</th> <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $stmt_orders = $pdo->query("SELECT o.*, p.title as product_title FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC");
                                while ($order = $stmt_orders->fetch(PDO::FETCH_ASSOC)) {
                                    $status_map = ['pending' => 'পেন্ডিং', 'confirmed' => 'কনফার্মড', 'shipped' => 'শিপড'];
                                    $status_badge_map = ['pending' => 'bg-warning text-dark', 'confirmed' => 'bg-success', 'shipped' => 'bg-info'];
                                    $status_text = $status_map[$order['status']] ?? ucfirst($order['status']);
                                    $status_badge = $status_badge_map[$order['status']] ?? 'bg-secondary';
                                    $ver_text = $order['is_verified'] ? 'ভেরিফাইড' : 'নট ভেরিফাইড';
                                    $ver_badge = $order['is_verified'] ? 'bg-success' : 'bg-danger';

                                    echo "<tr><td>{$order['id']}</td>";
                                    echo "<td><span class='badge {$status_badge}' id='status-badge-{$order['id']}'>{$status_text}</span></td>";
                                    echo "<td><span class='badge {$ver_badge}'>{$ver_text}</span></td>";
                                    echo "<td>" . htmlspecialchars($order['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['phone']) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['address']) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['product_title'] ?? 'N/A') . "</td>";
                                    echo "<td><input type='text' class='form-control form-control-sm note-input' data-id='{$order['id']}' value='" . htmlspecialchars($order['note'] ?? '') . "'></td>";
                                    echo "<td class='action-cell'>";
                                    echo "  <select class='form-select form-select-sm' id='status-select-{$order['id']}'>";
                                    echo "    <option value='pending'" . ($order['status'] == 'pending' ? ' selected' : '') . ">পেন্ডিং</option>";
                                    echo "    <option value='confirmed'" . ($order['status'] == 'confirmed' ? ' selected' : '') . ">কনফার্মড</option>";
                                    echo "    <option value='shipped'" . ($order['status'] == 'shipped' ? ' selected' : '') . ">শিপড</option>";
                                    echo "  </select>";
                                    echo "  <button class='btn btn-sm btn-primary btn-update-status' data-id='{$order['id']}'>আপডেট</button>";
                                    echo "</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div></div></div>
                    </div>

                    <div class="tab-pane fade" id="manage-products-panel" role="tabpanel">
                        <h2 class="mt-3">প্রোডাক্ট ম্যানেজ করুন</h2>
                        <div class="card"><div class="card-body"><div class="table-responsive">
                            <table id="products-table" class="table table-striped" style="width:100%">
                                <thead><tr><th>আইডি</th><th>ছবি</th><th>শিরোনাম</th><th>মূল্য</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
                                <tbody>
                                <?php
                                $stmt_products = $pdo->query("SELECT * FROM products ORDER BY id DESC");
                                while ($product = $stmt_products->fetch(PDO::FETCH_ASSOC)) {
                                    $status_text = $product['is_visible'] ? 'দৃশ্যমান' : 'লুকানো';
                                    $status_badge = $product['is_visible'] ? 'bg-success' : 'bg-secondary';
                                    $toggle_btn_text = $product['is_visible'] ? 'লুকান' : 'দেখান';
                                    $toggle_btn_class = $product['is_visible'] ? 'btn-warning' : 'btn-success';
                                    echo "<tr id='product-row-{$product['id']}'>";
                                    echo "<td>{$product['id']}</td>";
                                    echo "<td><img src='" . htmlspecialchars($product['images']) . "' class='product-thumb' alt='...'></td>";
                                    echo "<td class='prod-title'>" . htmlspecialchars($product['title']) . "</td>";
                                    if ($product['main_price'] > 0) {
                                        echo "<td class='prod-price'><del class='text-danger'>৳" . htmlspecialchars($product['main_price']) . "</del><br><strong class='text-success'>৳" . htmlspecialchars($product['price']) . "</strong></td>";
                                    } else {
                                        echo "<td class='prod-price'><strong class='text-success'>৳" . htmlspecialchars($product['price']) . "</strong></td>";
                                    }
                                    echo "<td><span class='badge {$status_badge} prod-status-badge'>{$status_text}</span></td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-info btn-edit' data-id='{$product['id']}' data-title='" . htmlspecialchars($product['title'], ENT_QUOTES) . "' data-price='" . htmlspecialchars($product['price']) . "' data-main_price='" . htmlspecialchars($product['main_price']) . "' data-description='" . htmlspecialchars($product['description'], ENT_QUOTES) . "' data-image='" . htmlspecialchars($product['images']) . "'>এডিট</button> 
                                            <button class='btn btn-sm {$toggle_btn_class} btn-toggle' data-id='{$product['id']}'>{$toggle_btn_text}</button> 
                                            <button class='btn btn-sm btn-danger btn-delete' data-id='{$product['id']}'>ডিলিট</button>
                                          </td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div></div></div>
                    </div>

                    <div class="tab-pane fade" id="add-product-panel" role="tabpanel">
                        <h2 class="mt-3">নতুন প্রোডাক্ট যোগ করুন</h2>
                        <div class="row"><div class="col-lg-6"><div class="card">
                            <div class="card-header">নতুন প্রোডাক্ট</div>
                            <div class="card-body">
                                <form id="add-product-form" enctype="multipart/form-data">
                                    <div id="product-alert" class="alert d-none"></div>
                                    <div class="mb-3"><label class="form-label">শিরোনাম</label><input type="text" class="form-control" name="title" required></div>
                                    <div class="row">
                                        <div class="col-md-6"><div class="mb-3">
                                            <label class="form-label">আসল মূল্য (ঐচ্ছিক)</label>
                                            <input type="number" step="0.01" class="form-control" name="main_price" placeholder="যেমন, ১২০০">
                                            <div class="form-text">ছাড় দেওয়ার আগে যে মূল্য ছিলো।</div>
                                        </div></div>
                                        <div class="col-md-6"><div class="mb-3">
                                            <label class="form-label">ছাড় মূল্য (আবশ্যক)</label>
                                            <input type="number" step="0.01" class="form-control" name="price" required placeholder="যেমন, ৯৯৯">
                                            <div class="form-text">ক্রেতা যে মূল্যে কিনবে।</div>
                                        </div></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">বিবরণ</label><textarea class="form-control" name="description" rows="3" required></textarea></div>
                                    <div class="mb-3"><label class="form-label">প্রোডাক্টের ছবি</label><input type="file" class="form-control" name="product_image" accept="image/*" required></div>
                                    <button type="submit" class="btn btn-primary" id="add-product-btn"><span id="add-product-btn-text">প্রোডাক্ট যোগ করুন</span><span id="add-product-btn-spinner" class="spinner-border spinner-border-sm d-none"></span></button>
                                </form>
                            </div>
                        </div></div></div>
                    </div>

                    <div class="tab-pane fade" id="create-page-panel" role="tabpanel">
                        <h2 class="mt-3">নতুন ল্যান্ডিং পেজ তৈরি করুন</h2>
                        <div class="row"><div class="col-lg-8"><div class="card">
                            <div class="card-header">পেজের বিবরণ</div>
                            <div class="card-body">
                                <form id="new-page-form" enctype="multipart/form-data">
                                    <div id="page-alert" class="alert d-none"></div>
                                    <div class="mb-3">
                                        <label for="new_page_title" class="form-label">টাইটেল<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="new_page_title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_page_description" class="form-label">বিবরণ (Description)</label>
                                        <textarea class="form-control" id="new_page_description" name="description" rows="4"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_page_slug" class="form-label">পেজের লিঙ্ক (Slug)<span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo rtrim(dirname("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"), '/\\');?>/page/</span>
                                            <input type="text" class="form-control" id="new_page_slug" name="slug" required placeholder="special-offer">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="new_page_product_id" class="form-label">প্রোডাক্ট লিঙ্ক করুন (ঐচ্ছিক)</label>
                                        <select class="form-select" id="new_page_product_id" name="product_id">
                                            <option value="">-- কোনো প্রোডাক্ট লিঙ্ক করবেন না --</option>
                                            <?php
                                            // 'products' টেবিল থেকে সব প্রোডাক্ট আনুন
                                            $stmt_all_products = $pdo->query("SELECT id, title FROM products WHERE is_visible = 1 ORDER BY title ASC");
                                            while ($prod = $stmt_all_products->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$prod['id']}'>" . htmlspecialchars($prod['title']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="form-text">যদি লিঙ্ক করা হয়, বাটনটি সরাসরি সেই প্রোডাক্টের চেকআউট পেজে নিয়ে যাবে।</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="new_page_button_text" class="form-label">বাটন টেক্সট<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="new_page_button_text" name="button_text" value="অর্ডার করতে কল করুন" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_page_images" class="form-label">ছবি (এক বা একাধিক)<span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="new_page_images" name="images[]" accept="image/*" required multiple>
                                        <div class="form-text">একাধিক ছবি সিলেক্ট করতে Ctrl (বা Cmd) চেপে ক্লিক করুন।</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="create-page-btn">
                                        <span id="create-page-btn-text">পেজ তৈরি করুন</span>
                                        <span id="create-page-btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                                    </button>
                                </form>
                            </div>
                        </div></div></div>
                    </div>

                    <?php if ($is_super): ?>
                    <div class="tab-pane fade" id="admins-panel" role="tabpanel">
                        <h2 class="mt-3">সাব-অ্যাডমিন ম্যানেজ করুন</h2>
                        
                        <div class="row mb-4"><div class="col-lg-6"><div class="card">
                            <div class="card-header">নতুন সাব-অ্যাডমিন যোগ করুন</div>
                            <div class="card-body">
                                <form id="add-admin-form">
                                    <div id="admin-alert" class="alert d-none"></div>
                                    <div class="mb-3"><label class="form-label">নাম</label><input type="text" class="form-control" name="name" required></div>
                                    <div class="mb-3"><label class="form-label">এমপ্লয়ী আইডি (লগইনের জন্য)</label><input type="text" class="form-control" name="employee_id" required></div>
                                    <div class="mb-3">
                                        <label class="form-label">পাসওয়ার্ড<span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="add-admin-btn">
                                        <span id="add-admin-btn-text">অ্যাডমিন তৈরি করুন</span>
                                        <span id="add-admin-btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                                    </button>
                                </form>
                            </div>
                        </div></div></div>
                        
                        <hr>

                        <h3 class="mt-4">বর্তমান সাব-অ্যাডমিন তালিকা</h3>
                        <div id="admin-action-alert" class="alert d-none"></div>
                        <div class="card"><div class="card-body"><div class="table-responsive">
                            <table id="admins-table" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>আইডি</th>
                                        <th>নাম</th>
                                        <th>এমপ্লয়ী আইডি</th>
                                        <th>স্ট্যাটাস</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $current_admin_id = $_SESSION['admin_id'];
                                $stmt_admins = $pdo->prepare("SELECT * FROM admins WHERE is_super = 0 AND id != ?");
                                $stmt_admins->execute([$current_admin_id]);
                                
                                while ($admin = $stmt_admins->fetch(PDO::FETCH_ASSOC)) {
                                    $status_text = $admin['is_active'] ? 'সচল (Active)' : 'নিষ্ক্রিয় (Deactivated)';
                                    $status_badge = $admin['is_active'] ? 'bg-success' : 'bg-secondary';
                                    $toggle_btn_text = $admin['is_active'] ? 'নিষ্ক্রিয় করুন' : 'সচল করুন';
                                    $toggle_btn_class = $admin['is_active'] ? 'btn-warning' : 'btn-success';

                                    echo "<tr id='admin-row-{$admin['id']}'>";
                                    echo "<td>{$admin['id']}</td>";
                                    echo "<td class='admin-name'>" . htmlspecialchars($admin['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($admin['employee_id']) . "</td>";
                                    echo "<td><span class='badge {$status_badge} admin-status-badge'>{$status_text}</span></td>";
                                    echo "<td>
                                            <button class='btn btn-sm {$toggle_btn_class} btn-toggle-admin' data-id='{$admin['id']}'>{$toggle_btn_text}</button> 
                                            <button class='btn btn-sm btn-danger btn-delete-admin' data-id='{$admin['id']}'>ডিলিট</button>
                                          </td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div></div></div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">প্রোডাক্ট এডিট করুন</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-product-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div id="edit-product-alert" class="alert d-none"></div>
                        <input type="hidden" id="edit_product_id" name="product_id">
                        <div class="mb-3"><label for="edit_title" class="form-label">শিরোনাম</label><input type="text" class="form-control" id="edit_title" name="title" required></div>
                        <div class="row">
                            <div class="col-md-6"><div class="mb-3">
                                <label for="edit_main_price" class="form-label">আসল মূল্য (ঐচ্ছিক)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_main_price" name="main_price" placeholder="যেমন, ১২০০">
                            </div></div>
                            <div class="col-md-6"><div class="mb-3">
                                <label for="edit_price" class="form-label">ছাড় মূল্য (আবশ্যক)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required placeholder="যেমন, ৯৯৯">
                            </div></div>
                        </div>
                        <div class="mb-3"><label for="edit_description" class="form-label">বিবরণ</label><textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea></div>
                        <div class="mb-3"><label class="form-label">বর্তমান ছবি</label><div><img src="" id="current_image_preview" class="product-thumb" alt="Current Image"></div></div>
                        <div class="mb-3">
                            <label for="product_image_edit" class="form-label">নতুন ছবি আপলোড করুন (ঐচ্ছিক)</label>
                            <input type="file" class="form-control" id="product_image_edit" name="product_image_edit" accept="image/*">
                            <div class="form-text">বর্তমান ছবি রাখতে চাইলে এটি খালি রাখুন।</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-primary" id="edit-product-btn">
                            <span id="edit-product-btn-text">সেভ করুন</span>
                            <span id="edit-product-btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#orders-table').DataTable({ "order": [[0, "desc"]] });
        var productsTable = $('#products-table').DataTable({ "order": [[0, "desc"]] });
        $('#admins-table').DataTable({ "order": [[0, "desc"]] }); // নতুন টেবিল

        function showFormAlert(alertId, msg, isSuccess) { 
            if(alertId === 'page-alert' && isSuccess) {
                $('#' + alertId).html(msg).removeClass('d-none alert-danger').addClass('alert-success');
                $('#' + alertId + ' a').css('color', '#0a3622'); // লিঙ্ক ঠিক করা
            } else {
                $('#' + alertId).text(msg).removeClass('d-none alert-danger alert-success').addClass(isSuccess ? 'alert-success' : 'alert-danger');
            }
            if (alertId === 'order-update-alert' || alertId === 'admin-action-alert') {
                setTimeout(() => { $('#' + alertId).addClass('d-none'); }, 3000);
            }
        }
        function setButtonLoading(btnId, isLoading) {
            const btn = $('#' + btnId); btn.prop('disabled', isLoading);
            btn.find('#' + btnId + '-text').toggleClass('d-none', isLoading);
            btn.find('#' + btnId + '-spinner').toggleClass('d-none', !isLoading);
        }

        const ordersTableBody = $('#orders-table tbody');
        ordersTableBody.on('click', '.btn-update-status', function() {
            const btn = $(this), id = btn.data('id'), new_status = $(`#status-select-${id}`).val();
            btn.prop('disabled', true).text('...'); 
            $.post('api/update_order.php', { id: id, status: new_status }, (res) => {
                btn.prop('disabled', false).text('আপডেট');
                if (res.success) {
                    const badge = $(`#status-badge-${id}`);
                    badge.removeClass('bg-warning bg-success bg-info text-dark');
                    let status_text = '';
                    if (new_status === 'pending') { badge.addClass('bg-warning text-dark'); status_text = 'পেন্ডিং'; } 
                    else if (new_status === 'confirmed') { badge.addClass('bg-success'); status_text = 'কনফার্মড'; } 
                    else { badge.addClass('bg-info'); status_text = 'শিপড'; }
                    badge.text(status_text);
                    showFormAlert('order-update-alert', `অর্ডার #${id} সফলভাবে "${status_text}"-এ আপডেট করা হয়েছে।`, true);
                } else {
                    showFormAlert('order-update-alert', `অর্ডার #${id} আপডেট করতে ব্যর্থ হয়েছে।`, false);
                }
            }, 'json');
        });
        ordersTableBody.on('blur', '.note-input', function() {
            const input = $(this); input.css('border-color', '#ffc107');
            $.post('api/update_note.php', { id: input.data('id'), note: input.val() }, (res) => {
                input.css('border-color', res.success ? '#198754' : '#dc3545');
                setTimeout(() => input.css('border', '1px dashed #ccc'), 2000);
            }, 'json');
        });

        $('#add-product-form').on('submit', function(e) {
            e.preventDefault(); setButtonLoading('add-product-btn', true);
            $.ajax({
                url: 'api/add_product.php', type: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json',
                success: (res) => {
                    showFormAlert('product-alert', res.message || 'প্রোডাক্ট যোগ হয়েছে!', res.success);
                    if (res.success) setTimeout(() => location.reload(), 1500);
                },
                error: (xhr) => showFormAlert('product-alert', xhr.responseJSON?.message || 'সার্ভার ত্রুটি।', false)
            }).always(() => setButtonLoading('add-product-btn', false));
        });

        $('#add-admin-form').on('submit', function(e) {
            e.preventDefault();
            setButtonLoading('add-admin-btn', true);
            $.post('api/add_admin.php', $(this).serialize(), (res) => {
                showFormAlert('admin-alert', res.message || 'অ্যাডমিন তৈরি হয়েছে!', res.success);
                if (res.success) {
                    this.reset();
                    setTimeout(() => location.reload(), 1500); // নতুন অ্যাডমিনকে টেবিলে দেখানোর জন্য রিলোড
                }
            }, 'json').fail((xhr) => showFormAlert('admin-alert', xhr.responseJSON?.message || 'সার্ভার ত্রুটি।', false))
            .always(() => setButtonLoading('add-admin-btn', false));
        });

        const productsTableBody = $('#products-table tbody');
        const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        productsTableBody.on('click', '.btn-edit', function() {
            const btn = $(this);
            $('#edit_product_id').val(btn.data('id'));
            $('#edit_title').val(btn.data('title'));
            $('#edit_price').val(btn.data('price'));
            $('#edit_main_price').val(btn.data('main_price'));
            $('#edit_description').val(btn.data('description'));
            $('#current_image_preview').attr('src', btn.data('image'));
            $('#product_image_edit').val('');
            $('#edit-product-alert').addClass('d-none');
            editModal.show();
        });
        $('#edit-product-form').on('submit', function(e) { e.preventDefault(); setButtonLoading('edit-product-btn', true); $.ajax({
            url: 'api/edit_product.php', type: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json',
            success: (res) => {
                if (res.success) { showFormAlert('edit-product-alert', res.message, true); setTimeout(() => { editModal.hide(); location.reload(); }, 1500); } 
                else { showFormAlert('edit-product-alert', res.message, false); }
            },
            error: (xhr) => showFormAlert('edit-product-alert', xhr.responseJSON?.message || 'সার্ভার ত্রুটি।', false)
        }).always(() => setButtonLoading('edit-product-btn', false)); });
        productsTableBody.on('click', '.btn-toggle', function() {
            const btn = $(this); const id = btn.data('id');
            $.post('api/toggle_product_visibility.php', { id: id }, (res) => {
                if (res.success) {
                    const badge = btn.closest('tr').find('.prod-status-badge');
                    if (res.new_status == 1) { badge.removeClass('bg-secondary').addClass('bg-success').text('দৃশ্যমান'); btn.removeClass('btn-success').addClass('btn-warning').text('লুকান'); } 
                    else { badge.removeClass('bg-success').addClass('bg-secondary').text('লুকানো'); btn.removeClass('btn-warning').addClass('btn-success').text('দেখান'); }
                }
            }, 'json');
        });
        productsTableBody.on('click', '.btn-delete', function() {
            const btn = $(this); const id = btn.data('id'); const title = btn.closest('tr').find('.prod-title').text();
            if (confirm(`আপনি কি "${title}" প্রোডাক্টটি ডিলিট করতে চান?`)) {
                $.post('api/delete_product.php', { id: id }, (res) => {
                    if (res.success) productsTable.row(btn.closest('tr')).remove().draw();
                    else alert('Error: ' + res.message);
                }, 'json');
            }
        });

        // *** নতুন: ল্যান্ডিং পেজ ফর্ম সাবমিট ***
        $('#new-page-form').on('submit', function(e) {
            e.preventDefault();
            setButtonLoading('create-page-btn', true);
            let formData = new FormData(this);

            $.ajax({
                url: 'api/add_landing_page.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: (res) => {
                    if (res.success) {
                        let successMsg = `পেজ তৈরি সফল! <br><strong>লিঙ্ক:</strong> <a href="${res.link}" target="_blank">${res.link}</a>`;
                        showFormAlert('page-alert', successMsg, true);
                        $('#new-page-form')[0].reset();
                    } else {
                        showFormAlert('page-alert', res.message, false);
                    }
                },
                error: (xhr) => showFormAlert('page-alert', xhr.responseJSON?.message || 'সার্ভার ত্রুটি।', false)
            }).always(() => setButtonLoading('create-page-btn', false));
        });

        // Slug অটো-জেনারেট (হেল্পার)
        $('#new_page_title').on('input', function() {
            let title = $(this).val();
            let slug = title.toLowerCase()
                .replace(/\s+/g, '-')       
                .replace(/[^\u0980-\u09FFa-z0-9-]+/g, '') 
                .replace(/--+/g, '-')       
                .replace(/^-+/, '')         
                .replace(/-+$/, '');        
            $('#new_page_slug').val(slug);
        });
        
        // --- নতুন: অ্যাডমিন ম্যানেজমেন্ট JS ---
        const adminsTableBody = $('#admins-table tbody');

        adminsTableBody.on('click', '.btn-toggle-admin', function() {
            const btn = $(this);
            const id = btn.data('id');
            const name = btn.closest('tr').find('.admin-name').text();
            
            if (confirm(`আপনি কি "${name}"-এর স্ট্যাটাস পরিবর্তন করতে চান?`)) {
                $.post('api/toggle_admin_status.php', { id: id }, (res) => {
                    if (res.success) {
                        const badge = btn.closest('tr').find('.admin-status-badge');
                        if (res.new_status == 1) { // Now Active
                            badge.removeClass('bg-secondary').addClass('bg-success').text('সচল (Active)');
                            btn.removeClass('btn-success').addClass('btn-warning').text('নিষ্ক্রিয় করুন');
                        } else { // Now Deactivated
                            badge.removeClass('bg-success').addClass('bg-secondary').text('নিষ্ক্রিয় (Deactivated)');
                            btn.removeClass('btn-warning').addClass('btn-success').text('সচল করুন');
                        }
                        showFormAlert('admin-action-alert', `"${name}"-এর স্ট্যাটাস পরিবর্তন করা হয়েছে।`, true);
                    } else {
                        showFormAlert('admin-action-alert', res.message || 'স্ট্যাটাস পরিবর্তন ব্যর্থ হয়েছে।', false);
                    }
                }, 'json');
            }
        });

        adminsTableBody.on('click', '.btn-delete-admin', function() {
            const btn = $(this);
            const id = btn.data('id');
            const name = btn.closest('tr').find('.admin-name').text();

            if (confirm(`আপনি কি "${name}"-কে স্থায়ীভাবে ডিলিট করতে চান? এই কাজটি ফেরত আনা যাবে না।`)) {
                $.post('api/delete_admin.php', { id: id }, (res) => {
                    if (res.success) {
                        $('#admins-table').DataTable().row(btn.closest('tr')).remove().draw();
                        showFormAlert('admin-action-alert', `"${name}"-কে ডিলিট করা হয়েছে।`, true);
                    } else {
                        showFormAlert('admin-action-alert', res.message || 'ডিলিট করতে ব্যর্থ হয়েছে।', false);
                    }
                }, 'json');
            }
        });

    });
    </script>
</body>
</html>