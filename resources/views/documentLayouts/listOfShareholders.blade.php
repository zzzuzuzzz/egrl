<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        .field {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .no-p {
            padding: 0;
            margin: 0;
        }
        .mt-10 {
            margin-top: 10px;
        }




        .header {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-bottom: 2px solid black;
            padding-bottom: 1px;
        }




        .dateBlock {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .numberKube {
            display: flex;
            flex-direction: row;
        }
        .numItem {
            width: 20px;
            height: 20px;
            text-align: center;
        }
        .kube {
            padding: 3px;
            border: 1px solid black;
        }
        .dateDescription {
            font-size: 10px;
        }






        .addressInfo {
            display: flex;
        }
        .addressTitle {
            margin-right: 10px;
        }




        table {
            width: 100%;
        }
        tr {
            width: 100%;
        }
        .tableRowTitle {
            width: 30%;
        }


    </style>

</head>
<body>
<div class="field">
    <div class="header">
        <h3 class="no-p">Список участников</h3>
        <p class="no-p"><b>{{ $documentTitle }}</b></p>
    </div>
    <div class="dateBlock mt-10">
        <div class="numberKube">
            <div class="numItem kube">{{ $date[0] }}</div>
            <div class="numItem kube">{{ $date[1] }}</div>
            <div class="numItem "></div>
            <div class="numItem kube">{{ $date[2] }}</div>
            <div class="numItem kube">{{ $date[3] }}</div>
            <div class="numItem "></div>
            <div class="numItem kube">{{ $date[4] }}</div>
            <div class="numItem kube">{{ $date[5] }}</div>
            <div class="numItem kube">{{ $date[6] }}</div>
            <div class="numItem kube">{{ $date[7] }}</div>
        </div>
        <p class="dateDescription no-p">(указывается дата, на которую составлен список участников общества с ограниченной ответственностью)</p>
    </div>
    <div class="addressInfo mt-10">
        <p class="addressTitle no-p">Место нахождения общества</p>
        <p class="no-p">{{ $address }}</p>
    </div>
    <div class="mt-10">
        <table>
            <!--            <tr>-->
            <!--                <th class="tableTitle">Сведения об Обществе с ограниченной ответственностью «ЛУЧ»</th>-->
            <!--            </tr>-->
            <tr>
                <td class="tableRowTitle">Сокращенное наименование общества</td>
                <td>{{ $shortName }}</td>
            </tr>
            <tr>
                <td class="tableRowTitle">Основной государственный регистрационный номер</td>
                <td>{{ $mainGosNumber }}</td>
            </tr>
            <tr>
                <td class="tableRowTitle">ИНН/КПП</td>
                <td>{{ $inn }}</td>
            </tr>
            <tr>
                <td class="tableRowTitle">Наименование государственного органа, осуществившего регистрацию общества</td>
                <td>{{ $gosCompanyName }}</td>
            </tr>
            <tr>
                <td class="tableRowTitle">Почтовый адрес (почтовый индекс, страна, регион область, край), город, улица, дом)</td>
                <td>{{ $mailIndexAddress }}</td>
            </tr>
            <tr>
                <td class="tableRowTitle">Размер уставного капитала (руб.)</td>
                <td>{{ $lengthMoneyRules }}</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
