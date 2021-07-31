<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/tailwind.style.css.php" />
    <link rel="stylesheet" href="/assets/tailwind.index.css" />
    <link rel="stylesheet" href="/assets/custom.css" />
    <link rel="stylesheet" href="/assets/fontawesome.min.css" />
    <title>Reset Password - <?= Page::$pageData->va_name ?></title>
    <script src="/assets/js/tailwind.js"></script>
</head>

<body>
    <div class="flex justify-center min-h-screen px-4 py-12 bg-gray-100 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <h3 class="text-xl font-bold text-center text-gray-500"><?= Page::$pageData->va_name ?></h3>
                <h2 class="text-4xl font-bold text-center text-gray-900">
                    Reset Password
                </h2>
            </div>
            <form class="block p-6 space-y-6 bg-white border border-gray-200 rounded-lg shadow-lg" method="POST">
                <?php if (Session::exists('success')) : ?>
                    <p class="text-sm font-bold text-center text-green-600"><?= Session::flash('success') ?></p>
                <?php elseif (Session::exists('error')) : ?>
                    <p class="text-sm font-bold text-center text-red-500"><?= Session::flash('error') ?></p>
                <?php else : ?>
                    <p class="text-sm text-center">
                        Your password has been set as a temporary password. This means either a staff member would like you to change
                        your password, or your password has been changed recently and needs to be set to something permanent. To help
                        keep your account secure, you are required to choose a new, permanent password. This will take effect immediately.
                    </p>
                <?php endif; ?>
                <input hidden type="hidden" name="token" value="<?= Token::generate() ?>" />
                <div>
                    <label for="newpass" class="block mb-1 font-semibold text-gray-700">New Password</label>
                    <input id="newpass" name="newpass" type="password" required class="relative block w-full px-3 py-2 text-gray-900 border-gray-400 rounded appearance-none focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                </div>

                <div>
                    <button type="submit" class="relative flex justify-center w-full px-4 py-2 text-sm font-medium border border-transparent rounded-md text-primary-text bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 hover:shadow-lg">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/alpinejs@3.0.1/dist/cdn.min.js" defer></script>
</body>

</html>