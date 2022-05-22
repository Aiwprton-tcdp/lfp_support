<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Отклики</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/answer.css"/>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <h4>Отдел продаж</h4>
                <select id="selectSalesDepartment" multiple="multiple"></select>
            </div>
            <div class="col">
                <h4>Должность</h4>
                <select id="selectSpecialty" multiple="multiple"></select>
            </div>
            <div class="col">
                <h4>Назначена встреча</h4>
                <input type="datetime-local" id="start" name="trip-start" min="2021-01-01" max="2023-01-01">-
                <input type="datetime-local" id="end" name="trip-end" min="2021-01-01" max="2023-01-01">
            </div>
        </div>
        <button type="button" class="btn btn-outline-success pull-right" onclick="getDeals()">Найти</button>
        <br>
        <div class="row">
        <!-- border-right border-dark -->
            <div class="col"> 
                <h4>Собеседования</h4>
                <table id="data" class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Дело</th>
                            <th scope="col">Собеседование</th>
                            <th scope="col">Должность</th>
                            <th scope="col">Отдел продаж</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Комментарий</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="col">
                <h4>Стажировка</h4>
                <table id="data2" class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Дело</th>
                            <th scope="col">Собеседование</th>
                            <th scope="col">Должность</th>
                            <th scope="col">Отдел продаж</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Комментарий</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
    <script src="//api.bitrix24.com/api/v1/"></script>
    <script src="/js/answer.js"></script>
</body>
</html><?php /**PATH C:\OpenServer\domains\new_cabinet\resources\views/app/answer.blade.php ENDPATH**/ ?>