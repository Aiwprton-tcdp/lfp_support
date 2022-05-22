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

            BX24.callMethod(
                'bizproc.activity.delete',
                {
                    'CODE': 'time_zone_converter'
                },
                function(result)
                {
                    console.log(result);
                    if(result.error())
                        alert("Error: " + result.error());
                }
            );

            BX24.callMethod(
                'bizproc.activity.add',
                {
                    'CODE': 'time_zone_converter',
                    'HANDLER': 'https://otkliki.sms19.ru/api/getTime',
                    'AUTH_USER_ID': 1,
                    'USE_SUBSCRIPTION': 'Y',
                    'NAME': {
                        'ru': 'Конвертер часовых поясов'
                    },
                    'DESCRIPTION': {
                        'ru': 'Вывод времени с учётом часовых поясов'
                    },
                    'PROPERTIES': {
                        'date': {
                            'Name': {
                                'ru': 'Дата задачи'
                            },
                            'Type': 'datetime',
                            'Required': 'Y'
                        },
                        'department': {
                            'Name': {
                                'ru': 'Отдел продаж'
                            },
                            'Type': 'string',
                            'Required': 'Y'
                        }
                    },
                    'RETURN_PROPERTIES': {
                        'status': {
                            'Name': {
                                'ru': 'Статус'
                            },
                            'Type': 'integer',
                            'Default': null
                        },
                        'client_date_and_time': {
                            'Name': {
                                'ru': 'Дата и время клиента'
                            },
                            'Type': 'string',
                            'Default': null
                        },
                        'employees_date_and_time': {
                            'Name': {
                                'ru': 'Дата и время сотрудника'
                            },
                            'Type': 'string',
                            'Default': null
                        },
                        'server_date_and_time': {
                            'Name': {
                                'ru': 'Серверная дата и время'
                            },
                            'Type': 'string',
                            'Default': null
                        }
                    }
                },
                function(result)
                {
                    console.log(result);
                    if(result.error())
                        alert("Error: " + result.error());
                }
            );
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
</html><?php /**PATH /var/www/www-root/data/www/sms19.ru/otkliki/resources/views/app/install.blade.php ENDPATH**/ ?>