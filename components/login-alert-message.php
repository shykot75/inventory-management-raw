<div class="mt-2 mb-2 w-full">

    <!-- Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="relative p-3 pr-12 text-sm bg-green-100 border border-green-500 rounded-md text-green-500">
            <button class="absolute top-0 bottom-0 right-0 p-3 text-green-200 transition hover:text-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" data-lucide="x"
                     class="h-5">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
            <span class="font-bold">Success</span> <?php echo $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="relative p-3 pr-12 text-sm bg-red-100 border border-red-500 rounded-md text-red-500">
            <button class="absolute top-0 bottom-0 right-0 p-3 text-red-200 transition hover:text-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" data-lucide="x"
                     class="h-5">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
            <span class="font-bold">Error</span> <?php echo $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Warning Message -->
    <?php if (isset($_SESSION['warning'])): ?>
        <div class="relative p-3 pr-12 text-sm bg-yellow-100 border border-yellow-500 rounded-md text-orange-500">
            <button class="absolute top-0 bottom-0 right-0 p-3 text-yellow-200 transition hover:text-yellow-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" data-lucide="x"
                     class="h-5">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
            <span class="font-bold">Warning</span> <?php echo $_SESSION['warning']; ?>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <!-- Validation Errors -->
    <?php if (!empty($errors)): ?>
        <div class="flex gap-3 p-4 text-sm text-orange-500 rounded-md bg-yellow-100 dark:bg-yellow-400/20 border border-yellow-500 rounded-md">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" data-lucide="alert-triangle"
                 class="inline-block size-4 mt-0.5 shrink-0">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <path d="M12 9v4"></path>
                <path d="M12 17h.01"></path>
            </svg>
            <div>
                <h6 class="mb-1">Uh oh, something went wrong!</h6>
                <ul class="ml-2 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

</div>
