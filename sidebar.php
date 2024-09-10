

<aside id="sidebar" class="sidebar border-r dark:border-gray-800 dark:shadow-lg bg-light text-light dark:bg-dark dark:text-dark">
    <!-- Component Start -->
    <div class="app-menu flex flex-col w-full h-full">
        <ul class="space-y-2 w-full px-2 mt-2">

            <li class="relative menu-item">
                <a href="dashboard.php" class="parent-item active flex items-center w-full px-3 py-3">
                    <i class="fa-solid fa-house text-sm "></i>
                    <span class="menu-title ml-4 text-sm font-medium">Dashboard</span>
                </a>
            </li>

            <?php if($_SESSION['user_role'] === 'admin'): ?>

            <!-- Product Dropdown Start -->
            <li class="relative menu-item">
                <div class="parent-item flex items-center flex-row w-full px-3 py-3 cursor-pointer">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                    <span class="menu-title ml-4 text-sm font-medium grow">Product</span>
                    <i class="fa-solid fa-angle-down arrow-icon"></i>
                </div>
                <ul class="dropdown-menu bg-white text-light dark:bg-dark dark:text-dark ml-2">
                    <li class="dropdown-item">
                        <a href="product-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Product List</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="product-create.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Add Product</span>
                        </a>
                    </li>

                </ul>
            </li>
            <!-- Product Dropdown End -->

            <!-- Purchase Dropdown Start -->
            <li class="relative menu-item">
                <div class="parent-item flex items-center flex-row w-full px-3 py-3 cursor-pointer">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                    <span class="menu-title ml-4 text-sm font-medium grow">Purchase</span>
                    <i class="fa-solid fa-angle-down arrow-icon"></i>
                </div>
                <ul class="dropdown-menu bg-white text-light dark:bg-dark dark:text-dark ml-2">
                    <li class="dropdown-item">
                        <a href="purchase-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Purchase List</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="purchase-create.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Create Purchase</span>
                        </a>
                    </li>

                </ul>
            </li>
            <!-- Purchase Dropdown End -->

            <!-- Purchase Return Dropdown Start -->
            <li class="relative menu-item">
                <a href="purchase-return-list.php" class="parent-item flex items-center w-full px-3 py-3">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                    <span class="menu-title ml-4 text-sm font-medium">Purchase Return</span>
                </a>
            </li>
            <!-- Purchase Return Dropdown End -->

            <!-- Sales Dropdown Start -->
            <li class="relative menu-item">
                <div class="parent-item flex items-center flex-row w-full px-3 py-3 cursor-pointer">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                    <span class="menu-title ml-4 text-sm font-medium grow">Sales</span>
                    <i class="fa-solid fa-angle-down arrow-icon"></i>
                </div>
                <ul class="dropdown-menu bg-white text-light dark:bg-dark dark:text-dark ml-2">
                    <li class="dropdown-item">
                        <a href="sales-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Sales List</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="sales-create.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Create Sales</span>
                        </a>
                    </li>

                </ul>
            </li>
            <!-- Sales Dropdown End -->

            <!-- Sales Return Dropdown Start -->
            <li class="relative menu-item">
                <a href="sales-return-list.php" class="parent-item flex items-center w-full px-3 py-3">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                    <span class="menu-title ml-4 text-sm font-medium">Sales Return</span>
                </a>
            </li>
            <!-- Sales Return Dropdown End -->

            <!-- User Management Dropdown Start -->
                <li class="relative menu-item">
                    <div class="parent-item flex items-center flex-row w-full px-3 py-3 cursor-pointer">
                        <i class="fas fa-user-shield text-sm"></i>
                        <span class="menu-title ml-4 text-sm font-medium grow">User Management</span>
                        <i class="fa-solid fa-angle-down arrow-icon"></i>
                    </div>
                    <ul class="dropdown-menu bg-white text-light dark:bg-dark dark:text-dark ml-2">
                        <li class="dropdown-item">
                            <a href="user-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                                <i class="fa-solid fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium">Users List</span>
                            </a>
                        </li>
                        <li class="dropdown-item">
                            <a href="user-create.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                                <i class="fa-solid fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium">Add User</span>
                            </a>
                        </li>

                    </ul>
                </li>
            <!-- User Management Dropdown End -->

            <?php endif ?>

            <!-- Reports Dropdown Start -->
            <li class="relative menu-item">
                <div class="parent-item flex items-center flex-row w-full px-3 py-3 cursor-pointer">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="menu-title ml-4 text-sm font-medium grow">Reports</span>
                    <i class="fa-solid fa-angle-down arrow-icon"></i>
                </div>
                <ul class="dropdown-menu bg-white text-light dark:bg-dark dark:text-dark ml-2">
                    <li class="dropdown-item">
                        <a href="purchase-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Purchase</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="purchase-return-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Purchase Return</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="sales-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Sales</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="sales-return-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                            <i class="fa-solid fa-chevron-right"></i>
                            <span class="ml-4 text-sm font-medium">Sales Return</span>
                        </a>
                    </li>

                </ul>
            </li>
            <!-- Reports Dropdown End -->

        </ul>
    </div>
</aside>

