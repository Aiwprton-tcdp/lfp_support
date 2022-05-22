<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title inertia><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="<?php echo e(mix('css/app.css')); ?>">
        <link rel="shortcut icon" href="/favicon.png" type="image/png">
        <link rel="stylesheet" type="text/css" href="/css/tailwind.min.css">
        <link rel="stylesheet" type="text/css" href="/css/jquery.dataTables.min.css">
        <!-- <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>"> -->

        <!-- Scripts -->
        <?php echo app('Tightenco\Ziggy\BladeRouteGenerator')->generate(); ?>
        <script src="<?php echo e(mix('js/app.js')); ?>" defer></script>
    </head>
    <body class="overscroll-contain h-screen items-center justify-center" style="background: #edf2f7;">
        <div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div>
    </body>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
</html>
<?php /**PATH D:\Programming\OpenServer\domains\lfp_support.ru\resources\views/app.blade.php ENDPATH**/ ?>