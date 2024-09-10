<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

// Check if the user is admin
if ($_SESSION['user_role'] !== 'admin') {
    // If not logged in, redirect to dashboard page
    $_SESSION['error'] = "You don't have permission to access this page";
    header('Location: dashboard.php');
    exit();
}

include 'config.php'; // Include the database connection

// Fetch users from the database
function getUsers($connection) {
    $query = "SELECT id, name, email, phone, role, status FROM users WHERE deleted_at IS NULL";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

$users = getUsers($connection);  // Fetch users

// Handle product deletion
if (isset($_POST['delete'])) {
    $user_id = $_POST['id'];

    // Delete query using traditional mysqli_query
    $delete_query = "DELETE FROM users WHERE id = '$user_id'";

    if (mysqli_query($connection, $delete_query)) {
        $_SESSION['success'] = "User deleted successfully";
        // Redirect to the product list page after successful deletion
        header("Location: user-list.php");
        exit();
    } else {
        $_SESSION['error'] = "Error deleting user: " . mysqli_error($connection);
    }
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User List | IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

    </style>
</head>
<body class="w-full bg-body-light text-light dark:bg-dark dark:text-dark">

<!-- Navbar Start -->
<?php include('navbar.php'); ?>
<!-- Navbar End -->

<div class="flex">
    <!-- Sidebar Start -->
    <?php include('sidebar.php'); ?>
    <!-- Sidebar End -->

    <!-- Main Content Start -->
    <main class="w-full min-h-full pt-4 px-4 pb-12 text-light dark:bg-black dark:text-dark">

        <?php
        // Replace this with your PHP login alert logic
        include('components/login-alert-message.php');
        ?>

        <div class="main-body">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-4 text-15">User Lists</h6>
                    <table id="borderedTable" class="bordered group" style="width:100%">
                        <thead>
                        <tr>
                            <th>SL</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr class="text-sm text-center">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($user['name'] ?? '--'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? '--'); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?? '--'); ?></td>
                                    <td><?php echo htmlspecialchars($user['role'] ?? '--'); ?></td>
                                    <td>
                                        <div class="<?php echo ($user['status'] == '1') ? 'badge-active' : 'badge-inactive'; ?> px-2.5 py-0.5 inline-block">
                                            <?php echo ($user['status'] == '1') ? 'Active' : 'Inactive'; ?>
                                        </div>
                                    </td>



                                    <td>
                                        <div class="flex justify-center">
                                            <div class="dropdown relative">
                                                <button type="button"
                                                        class="text-white dropdown-toggle btn bg-primary-500 border-primary-500 hover:text-white hover:bg-primary-600 focus:text-white focus:bg-primary-600"
                                                        id="dropdownMenuheading-<?php echo $user['id']; ?>" onclick="toggleDropdown(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul id="dropdownMenu-<?php echo $user['id']; ?>" class="absolute z-50 py-2 mt-1 list-none bg-white rounded-md shadow-md dropdown-toggle-menu min-w-max hidden">
                                                    <li>
                                                        <a class="block px-4 py-1.5 text-base font-medium text-slate-600 dropdown-item"
                                                           href="user-update.php?id=<?php echo $user['id']; ?>">
                                                            Edit
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="block px-4 py-1.5 text-base font-medium text-slate-600 dropdown-item"
                                                           href="user-password-update.php?id=<?php echo $user['id']; ?>">
                                                            Change Password
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <?php if ($user['role'] != 'admin'): ?>
                                                            <form action="user-list.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" name="delete" class="block px-4 py-1.5 text-base font-medium text-slate-600 dropdown-item">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No users found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <!-- Main Content End -->
</div>


<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>


<!--dataTables-->
<script type="module" src="assets/libs/datatables/jquery-3.7.0.js"></script>
<script type="module" src="assets/libs/datatables/data-tables.min.js"></script>
<script type="module" src="assets/libs/datatables/data-tables.tailwindcss.min.js"></script>

<script type="module" src="assets/js/datatable-util.js"></script>
<script type="module" src="assets/js/datatables.js"></script>
<script>
    function confirmDelete(productId) {
        if (confirm("Are you sure you want to delete this item?")) {
            document.getElementById('delete-form-' + productId).submit();
        }
    }
</script>

<script>
    function toggleDropdown(id) {
        var dropdownMenu = document.getElementById('dropdownMenu-' + id);
        dropdownMenu.classList.toggle('hidden');
    }
</script>

</body>
</html>

