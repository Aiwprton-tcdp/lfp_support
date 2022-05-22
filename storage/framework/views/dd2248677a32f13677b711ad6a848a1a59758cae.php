<!DOCTYPE html>
<html lang="en">
    <?php if($result['rest_only'] === false): ?>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="//api.bitrix24.com/api/v1/"></script>
        <?php if($result['install'] == true): ?>
        <script>
            BX24.init(function(){
                console.log(BX24.installFinish());
            });
        </script>
        <?php endif; ?>
    </head>
    <body>
        <?php if($result['install'] == true): ?>
        <script>
        alert("awdawdawd");
        </script>
            Установка была завершена
        <?php else: ?>
            Ошибка установки
        <?php endif; ?>
    </body>
    <?php endif; ?>
</html><?php /**PATH D:\Programming\OpenServer\domains\lfp_support.ru\resources\views/app/install.blade.php ENDPATH**/ ?>