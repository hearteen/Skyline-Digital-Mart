<?php
session_start();
include '../includes/db_config.php';
include '../includes/lang.php';

// Dealer Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Category Add Logic
if (isset($_POST['add_category'])) {
    $name_en = mysqli_real_escape_string($conn, $_POST['cat_name_en']);
    $name_ta = mysqli_real_escape_string($conn, $_POST['cat_name_ta']);
    
    $cat_img = "default_cat.jpg";
    if (isset($_FILES['cat_image']) && $_FILES['cat_image']['error'] == 0) {
        $target_dir = "../uploads/categories/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES["cat_image"]["name"], PATHINFO_EXTENSION);
        $filename = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES["cat_image"]["tmp_name"], $target_dir . $filename)) {
            $cat_img = $filename;
        }
    }

    $sql = "INSERT INTO categories (cat_name_en, cat_name_ta, cat_image) VALUES ('$name_en', '$name_ta', '$cat_img')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Category added successfully! ✨";
        header("Location: manage_categories.php");
        exit();
    }
}

// Category Update Logic
if (isset($_POST['update_category'])) {
    $cat_id = (int)$_POST['cat_id'];
    $name_en = mysqli_real_escape_string($conn, $_POST['cat_name_en']);
    $name_ta = mysqli_real_escape_string($conn, $_POST['cat_name_ta']);
    
    $update_img_sql = "";
    if (isset($_FILES['cat_image']) && $_FILES['cat_image']['error'] == 0) {
        $target_dir = "../uploads/categories/";
        $ext = pathinfo($_FILES["cat_image"]["name"], PATHINFO_EXTENSION);
        $filename = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES["cat_image"]["tmp_name"], $target_dir . $filename)) {
            $update_img_sql = ", cat_image = '$filename'";
        }
    }

    $sql = "UPDATE categories SET cat_name_en='$name_en', cat_name_ta='$name_ta' $update_img_sql WHERE cat_id=$cat_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Category updated successfully! 🔄";
        header("Location: manage_categories.php");
        exit();
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY cat_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Categories | ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary: #10b981;
        --secondary: #00f2ff;
        --dark: #0f172a;
        --light-bg: #f4f7fe;
        --card-bg: #ffffff;
        --text-muted: #64748b;
        --danger: #ff4757;
        --radius: 16px;
        --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
    
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background-color: var(--light-bg); 
        margin: 0; padding: 0; color: var(--dark);
        line-height: 1.6;
    }

    .main-wrapper { padding: 20px 15px 100px 15px; }
    .container { max-width: 800px; margin: 0 auto; }

    /* Typography */
    h2 { font-weight: 800; font-size: 1.5rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    h2 i { color: var(--primary); }

    /* Glass Card Style */
    .admin-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: var(--shadow);
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Elements */
    .input-group { margin-bottom: 15px; }
    label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; }
    
    input[type="text"], input[type="file"] {
        width: 100%; padding: 14px; border-radius: 12px;
        border: 2px solid #e2e8f0; font-family: inherit; font-size: 1rem;
        transition: all 0.3s ease;
    }

    input:focus { border-color: var(--primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }

    .btn-submit {
        width: 100%; padding: 16px; border-radius: 12px; border: none;
        background: var(--dark); color: white; font-weight: 800; font-size: 1rem;
        cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-submit:active { transform: scale(0.98); }

    /* Line-by-Line List View (Mobile Optimized) */
    .category-list { display: flex; flex-direction: column; gap: 15px; }
    
    .category-item {
        background: white; border-radius: var(--radius); padding: 15px;
        display: flex; align-items: center; gap: 15px;
        border: 1px solid #edf2f7; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: 0.3s;
    }

    .category-item:hover { border-color: var(--primary); }

    .cat-thumb {
        width: 60px; height: 60px; border-radius: 12px;
        object-fit: cover; border: 2px solid #f1f5f9;
    }

    .cat-details { flex: 1; }
    .cat-details h4 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--dark); }
    .cat-details p { margin: 2px 0 0 0; font-size: 0.85rem; color: var(--primary); font-weight: 600; }

    .cat-actions { display: flex; gap: 12px; }
    .action-btn { 
        width: 38px; height: 38px; border-radius: 10px; 
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; cursor: pointer; transition: 0.2s;
    }
    .edit-btn { background: rgba(16, 185, 129, 0.1); color: var(--primary); }
    .del-btn { background: rgba(255, 71, 87, 0.1); color: var(--danger); text-decoration: none; }

    /* Modal Styling */
    .modal {
        display: none; position: fixed; inset: 0; z-index: 9999;
        background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px);
        align-items: center; justify-content: center; padding: 20px;
    }
    .modal-content {
        background: white; width: 100%; max-width: 400px;
        border-radius: 24px; padding: 25px; position: relative;
        animation: pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes pop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    /* Desktop View Adjustments */
    @media (min-width: 600px) {
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        .btn-submit { width: auto; padding: 14px 40px; margin-left: auto; }
    }
    </style>
</head>
<body>

    <?php include 'includes/admin_nav.php'; ?>

    <div class="main-wrapper">
        <div class="container">
            
            <h2><i class="fas fa-grid-2"></i> Category Master</h2>

            <?php if(isset($_SESSION['success'])): ?>
                <div style="background: var(--primary); color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem; text-align: center;">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <p style="margin-top: 0; font-weight: 800; font-size: 1.1rem; color: var(--primary);"><i class="fas fa-plus-circle"></i> Add New</p>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="input-group">
                            <label>English Name</label>
                            <input type="text" name="cat_name_en" placeholder="e.g. Fruits" required>
                        </div>
                        <div class="input-group">
                            <label>தமிழ் பெயர்</label>
                            <input type="text" name="cat_name_ta" placeholder="எ.கா. பழங்கள்" required>
                        </div>
                        <div class="input-group full-width">
                            <label>Category Image</label>
                            <input type="file" name="cat_image" accept="image/*" required>
                        </div>
                    </div>
                    <button type="submit" name="add_category" class="btn-submit">
                        <i class="fas fa-cloud-upload-alt"></i> Save Category
                    </button>
                </form>
            </div>

            <div class="category-list">
                <p style="font-weight: 800; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Manage List</p>
                
                <?php while($row = mysqli_fetch_assoc($categories)): ?>
                <div class="category-item">
                    <img src="../uploads/categories/<?php echo $row['cat_image']; ?>" class="cat-thumb" alt="icon">
                    <div class="cat-details">
                        <h4><?php echo $row['cat_name_en']; ?></h4>
                        <p><?php echo $row['cat_name_ta']; ?></p>
                    </div>
                    <div class="cat-actions">
                        <div class="action-btn edit-btn" onclick="openEditModal('<?php echo $row['cat_id']; ?>', '<?php echo $row['cat_name_en']; ?>', '<?php echo $row['cat_name_ta']; ?>')">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <a href="delete_category.php?id=<?php echo $row['cat_id']; ?>" class="action-btn del-btn" onclick="return confirm('Delete panna mudivu pannitingala?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>

    <div id="updateModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0"><i class="fas fa-edit"></i> Edit Category</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="cat_id" id="edit_cat_id">
                <div class="input-group">
                    <label>English Name</label>
                    <input type="text" name="cat_name_en" id="edit_cat_en" required>
                </div>
                <div class="input-group">
                    <label>தமிழ் பெயர்</label>
                    <input type="text" name="cat_name_ta" id="edit_cat_ta" required>
                </div>
                <div class="input-group">
                    <label>Change Image (Optional)</label>
                    <input type="file" name="cat_image" accept="image/*">
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" onclick="closeModal()" class="btn-submit" style="background:#e2e8f0; color:var(--dark);">Cancel</button>
                    <button type="submit" name="update_category" class="btn-submit" style="background:var(--primary);">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("updateModal");
        function openEditModal(id, en, ta) {
            document.getElementById("edit_cat_id").value = id;
            document.getElementById("edit_cat_en").value = en;
            document.getElementById("edit_cat_ta").value = ta;
            modal.style.display = "flex";
        }
        function closeModal() { modal.style.display = "none"; }
        window.onclick = function(e) { if (e.target == modal) closeModal(); }
    </script>

</body>
</html>