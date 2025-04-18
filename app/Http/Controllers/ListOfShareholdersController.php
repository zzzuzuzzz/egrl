<?php

namespace App\Http\Controllers;

use App\Contracts\Services\DocumentsServiceContract;
use App\Contracts\Services\ListOfShareholdersServiceContract;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class ListOfShareholdersController extends Controller
{
    private function dateWithMonthRu(): string
    {
        $arr = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря'
        ];
        $month = date('n')-1;

        return date('d ') . $arr[$month] . date(' Y ') . 'года';
    }
    private function createFullAddress(array $document): string
    {
        if (isset($document['СвЮЛ']['СвАдресЮЛ']['АдресРФ'])) {
            $addressFromDocument = $document['СвЮЛ']['СвАдресЮЛ']['АдресРФ'];

            $fullAddress = $addressFromDocument['@attributes']['Индекс']
                . ', ' . $addressFromDocument['Регион']['@attributes']['НаимРегион']
                . ', ' . $addressFromDocument['Улица']['@attributes']['ТипУлица']
                . ' ' . $addressFromDocument['Улица']['@attributes']['НаимУлица']
                . ', ' . $addressFromDocument['@attributes']['Дом'];

        } else {
            $addressFromDocument = $document['СвЮЛ']['СвАдресЮЛ']['СвАдрЮЛФИАС'];

            $fullAddress = $addressFromDocument['@attributes']['Индекс']
                . ', ' . $addressFromDocument['НаимРегион']
                . ', ' . $addressFromDocument['МуниципРайон']['@attributes']['Наим']
                . ', ' . $addressFromDocument['ЭлУлДорСети']['@attributes']['Тип']
                . ' ' . $addressFromDocument['ЭлУлДорСети']['@attributes']['Наим'];

            if (isset($addressFromDocument['Здание']['@attributes'])) {
                $fullAddress .= ', ' . $addressFromDocument['Здание']['@attributes']['Тип'] . ' ' . $addressFromDocument['Здание']['@attributes']['Номер'];
            } else {
                foreach ($addressFromDocument['Здание'] as $item) {
                    $fullAddress .= ', ' . $item['@attributes']['Тип'] . ' ' . $item['@attributes']['Номер'];
                }
            }

            if (isset($addressFromDocument['ПомещЗдания'])) {
                $fullAddress = $fullAddress . ', ' . $addressFromDocument['ПомещЗдания']['@attributes']['Тип'] . ' ' . $addressFromDocument['ПомещЗдания']['@attributes']['Номер'];
            }
        }

        return $fullAddress;
    }
    private function getAuthorizedCapital(array $document): string
    {
        $authorizedCapital = 'Не удалось выяснить программно';

        if (isset($document['СвЮЛ']['СвУстКап']['@attributes']['СумКап'])) {
            $authorizedCapital = $document['СвЮЛ']['СвУстКап']['@attributes']['СумКап'];
        } else {
            foreach ($document['СвЮЛ']['СвУчредит'] as $item) {
                if (isset($item['ДоляУстКап']['РазмерДоли']) && $item['ДоляУстКап']['РазмерДоли']['Процент'] == '100') {
                    $authorizedCapital = $item['ДоляУстКап']['@attributes']['НоминСтоим'];
                    break;
                }
                dd($item);
            }
        }

        return $authorizedCapital;
    }
    private function createEncumbrance(array $document): string
    {
        $filteredArray = [];

        foreach ($document as $groupKey => $groupValue) { // проходимся по участникам
            foreach ($groupValue as $key => $value) { // проходимся по ключу участника
                if ($key == 'ГРНДатаПерв') { // проверка на вложенность массива второго уровня
                    if (isset($groupValue['СвОбрем'])) { // проверка, что есть обременения
                        if (isset($groupValue['СвОбрем']['@attributes'])) { // Проверка, что обременение одно
                            if ($groupValue['СвОбрем']['@attributes']['ВидОбрем'] != "ЗАЛОГ") {
                                continue;
                            }
                            if (!array_key_exists($groupValue['СвОбрем']['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'], $filteredArray)) {
                                $filteredArray += [$groupValue['СвОбрем']['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'] => []];
                            }
                            $filteredArray[$groupValue['СвОбрем']['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн']][] = 'Номер договора залога: ' . $groupValue['СвОбрем']['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Номер'] . ' Дата договора залога: ' . $groupValue['СвОбрем']['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Дата'];
                        } elseif (isset($groupValue['СвОбрем'][0]['@attributes'])) { // Если больше 1
                            foreach($groupValue['СвОбрем'] as $item) {
                                if ($item['@attributes']['ВидОбрем'] != "ЗАЛОГ") {
                                    continue;
                                }
                                if (!array_key_exists($item['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'], $filteredArray)) {
                                    $filteredArray += [$item['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'] => []];
                                }
                                $filteredArray[$item['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн']][] = 'Номер договора залога: ' . $item['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Номер'] . ' Дата договора залога: ' . $item['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Дата'];
                            }
                        }
                    }
                    continue;
                }
                if (isset($value['СвОбрем'])) { // проверка, что есть обременения
                    if (isset($value['СвОбрем']['@attributes'])) { // Проверка, что обременение одно
                        if ($value['СвОбрем']['@attributes']['ВидОбрем'] != "ЗАЛОГ") {
                            continue;
                        }
                        if (!array_key_exists($value['СвОбрем']['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'], $filteredArray)) {
                            $filteredArray += [$value['СвОбрем']['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'] => []];
                        }
                        $filteredArray[$value['СвОбрем']['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн']][] = 'Номер договора залога: ' . $value['СвОбрем']['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Номер'] . ' Дата договора залога: ' . $value['СвОбрем']['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Дата'];
                    } elseif (isset($value['СвОбрем'][0]['@attributes'])) { // Если больше 1
                        foreach($value['СвОбрем'] as $item) {
                            if ($item['@attributes']['ВидОбрем'] != "ЗАЛОГ") {
                                continue;
                            }
                            if (!array_key_exists($item['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'], $filteredArray)) {
                                $filteredArray += [$item['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'] => []];
                            }
                            $filteredArray[$item['СвЗалогДержЮЛ']['НаимИННЮЛ']['@attributes']['НаимЮЛПолн']][] = 'Номер договора залога: ' . $item['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Номер'] . ' Дата договора залога: ' . $item['СвЗалогДержЮЛ']['СвНотУдДогЗал']['@attributes']['Дата'];
                        }
                    }
                };
            }
        }

        $resultStr = '';

        foreach ($filteredArray as $bankName => $value) {
            $resultStr .= $bankName . ': ';
            foreach ($value as $item) {
                $resultStr .= $item . ' ';
            }
        }
        return $resultStr ? $resultStr : 'нет';
    }
    public function makeListOfShareholders (
        Request $request,
        ListOfShareholdersServiceContract $listOfShareholdersService,
        DocumentsServiceContract $documentsService
    ) {
        // ИНН компании
        $inn = $request->get('inn');

        // Получаем выписку из ЕГРЮЛ и сохраняем ее в БД
        $document = $documentsService->getDocumentFromFNS($inn);
//        $requestDocument = $documentsService->saveDocument($inn, $document);

        // Переменные для выписки участников
        $fullAddress = $this->createFullAddress($document); // Полный адрес общества
        $authorizedCapital = $this->getAuthorizedCapital($document); // Уставной капитал
        if ($authorizedCapital != 'Не удалось выяснить программно') {
            number_format($authorizedCapital, 0, '', ' ');
        }








        // Создаем новый документ
        $phpWord = new PhpWord();


        // Настройки стилей (по желанию, для единообразия)
        $fontStyleHeader = [
            'name' => 'Times New Roman',
            'size' => 14,
            'bold' => true,
        ];
        $fontStyleRegular = [
            'name' => 'Times New Roman',
            'size' => 12,
        ];
        $paragraphStyleCenter = [
            'alignment' => Jc::CENTER,
        ];
        $paragraphStyleLeft = [
            'alignment' => Jc::LEFT,
        ];
        // Стиль для рамки ячейки с датой
        $cellBorderStyle = [
            'borderTopSize' => 1,
            'borderBottomSize' => 1,
            'borderLeftSize' => 1,
            'borderRightSize' => 1,
            'borderTopColor' => '000000',
            'borderBottomColor' => '000000',
            'borderLeftColor' => '000000',
            'borderRightColor' => '000000',
        ];
        // Стиль для футера
        $footerStyle = [
            'alignment' => Jc::RIGHT,
        ];
        $firstRowStyle = ['bgColor' => 'cccccc'];
        $fontStyleTable = [
            'bold' => true,
            'size' => 10,
            'name' => 'Times New Roman',
        ];
        // Создаем секцию и устанавливаем ориентацию страницы
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginTop' => 200,
            'marginLeft' => 400,
            'marginRight' => 200,
            'marginBottom' => 200,
        ]);


        //  Добавляем шапку на каждую страницу
        $header = $section->addHeader();
        $header->addText('СПИСОК УЧАСТНИКОВ', $fontStyleHeader, $paragraphStyleCenter);
        $header->addText($document['СвЮЛ']['СвНаимЮЛ']['@attributes']['НаимЮЛПолн'], $fontStyleHeader, $paragraphStyleCenter);
        // Добавляем линию в шапку
        $lineStyle = [
            'weight' => 1,
            'width' => 1000,
            'height' => 0,
            'color' => '000000',
        ];
        $header->addLine($lineStyle);


        // Добавляем футер с нумерацией страниц
        $footer = $section->addFooter();
        $footer->addPreserveText('{PAGE}', null, $footerStyle);


        // Дата в квадратиках
        $tableDate = $section->addTable();
        $tableDate->addRow();
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[10], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[11], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[7], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[8], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[2], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[3], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[4], $fontStyleRegular, $paragraphStyleCenter);
        $tableDate->addCell(250, $cellBorderStyle)->addText(date('HY-m-d')[5], $fontStyleRegular, $paragraphStyleCenter);

        $section->addText('(указывается дата, на которую составлен список участников общества с ограниченной ответственностью)', $fontStyleRegular, $paragraphStyleCenter);
        $section->addTextBreak();

        // Место нахождения общества
        $section->addText('Место нахождения общества              ' . $fullAddress, $fontStyleRegular, $paragraphStyleLeft);
        $section->addTextBreak();

        // Сведения об обществе - Таблица
        $tableStyle = [
            'borderSize' => 1,  // Minimum value for border
            'borderColor' => '000000',
            'cellMargin' => 80,
        ];

        $table = $section->addTable($tableStyle);

        $table->addRow();
        $cell = $table->addCell(10000);
        $cell->getStyle()->setGridSpan(2);
        $cell->addText('Сведения об ' . $document['СвЮЛ']['СвНаимЮЛ']['@attributes']['НаимЮЛПолн'], $fontStyleHeader, $paragraphStyleCenter);

        $table->addRow();
        $table->addCell(5000)->addText('Сокращенное наименование общества', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(10000)->addText($document['СвЮЛ']['СвНаимЮЛ']['СвНаимЮЛСокр']['@attributes']['НаимСокр'], $fontStyleRegular, $paragraphStyleLeft);

        $table->addRow();
        $table->addCell(5000)->addText('Основной государственный регистрационный номер', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(10000)->addText($document['СвЮЛ']['@attributes']['ОГРН'], $fontStyleRegular, $paragraphStyleLeft);

        $table->addRow();
        $table->addCell(5000)->addText('ИНН/КПП', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(10000)->addText($document['СвЮЛ']['@attributes']['ИНН'] . '/' . $document['СвЮЛ']['@attributes']['КПП'], $fontStyleRegular, $paragraphStyleLeft);

        $table->addRow();
        $table->addCell(5000)->addText('Наименование государственного органа, осуществившего регистрацию общества', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(10000)->addText($document['СвЮЛ']['СвРегОрг']['@attributes']['НаимНО'], $fontStyleRegular, $paragraphStyleLeft);

        $table->addRow();
        $table->addCell(5000)->addText('Почтовый адрес (почтовый индекс, страна, регион область, край), город, улица, дом)', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(10000)->addText($fullAddress, $fontStyleRegular, $paragraphStyleLeft);

        $table->addRow();
        $table->addCell(5000)->addText('Размер уставного капитала (руб.)', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(10000)->addText($authorizedCapital . ' рублей', $fontStyleRegular, $paragraphStyleLeft);
        $section->addTextBreak();

        // Общие сведения о размере и принадлежности долей
        $section->addText('Общие сведения о размере и принадлежности долей в уставном капитале:', $fontStyleHeader, $paragraphStyleLeft);

        $table = $section->addTable($tableStyle);

        // Заголовки таблицы
        $table->addRow();
        $table->addCell(1000, $firstRowStyle)->addText('№ п/п', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Наименование', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(2000, $firstRowStyle)->addText('Тип владельца доли (участник, общество)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Размер доли в уставном капитале общества (%)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(2000, $firstRowStyle)->addText('Номинальная стоимость доли в уставном капитале общества (руб.)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Сведения об оплате доли (руб.)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(6000, $firstRowStyle)->addText('Залог доли', $fontStyleTable, $paragraphStyleCenter);

        $count = 0;

        if (isset ($document['СвЮЛ']['СвДоляООО'])) {
            $table->addRow();
            $count = $count + 1;
            $table->addCell(1000)->addText($count, $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(1500)->addText('доля в уставном капитале', $fontStyleRegular, $paragraphStyleLeft);
            $table->addCell(2000)->addText('общество', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(1500)->addText($document['СвЮЛ']['СвДоляООО']['РазмерДоли']['Процент'] . ' %', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(2000)->addText(number_format($document['СвЮЛ']['СвДоляООО']['@attributes']['НоминСтоим'], 0, '', ' '), $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(1500)->addText('оплачена полностью', $fontStyleRegular, $paragraphStyleCenter);
//            $table->addCell(6000)->addText($this->createEncumbrance($document['СвЮЛ']['СвДоляООО']['СвОбрем']) ? $this->createEncumbrance($document['СвЮЛ']['СвДоляООО']['СвОбрем']) : 'нет', $fontStyleRegular, $paragraphStyleCenter);
        }

        $table->addRow();
        $count = $count + 1;
        $table->addCell(1000)->addText($count, $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(2000)->addText('доля в уставном капитале', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(2000)->addText('участник', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(1500)->addText(100 - (isset($document['СвЮЛ']['СвДоляООО']) ? $document['СвЮЛ']['СвДоляООО']['РазмерДоли']['Процент'] : 0) . ' %', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(2000)->addText(number_format(($this->getAuthorizedCapital($document) - (isset($document['СвЮЛ']['СвДоляООО']) ? $document['СвЮЛ']['СвДоляООО']['@attributes']['НоминСтоим'] : 0)), 0, '', ' '), $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(1500)->addText('оплачена полностью', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(6000)->addText($this->createEncumbrance($document['СвЮЛ']['СвУчредит']), $fontStyleRegular, $paragraphStyleCenter);

        // Итого
        $table->addRow();
        $table->addCell(1000)->addText('Всего:', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(1500)->addText('Уставный капитал', $fontStyleRegular, $paragraphStyleLeft);
        $table->addCell(2000)->addText('', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(1500)->addText('100 %', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(2000)->addText($authorizedCapital . ' рублей', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(1500)->addText('', $fontStyleRegular, $paragraphStyleCenter);
        $table->addCell(6000)->addText('', $fontStyleRegular, $paragraphStyleCenter);




        $section->addTextBreak();
        // Раздел 1. Список участников общества
        $section->addText('Раздел 1. Список участников общества с ограниченной ответственностью', $fontStyleHeader, $paragraphStyleLeft);
        $section->addText('Подраздел 1.1. Список участников ' . $document['СвЮЛ']['СвНаимЮЛ']['СвНаимЮЛСокр']['@attributes']['НаимСокр'] . ' на ' . $this->dateWithMonthRu(), $fontStyleRegular, $paragraphStyleLeft);

        // Таблица списка участников
        $table = $section->addTable($tableStyle);

        // Заголовки таблицы списка
        $table->addRow();
        $table->addCell(500, $firstRowStyle)->addText('№ п/п', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(3000, $firstRowStyle)->addText('Полное фирменное наименование (для юридического лица) или фамилии имя отчество для физического лица', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(3000, $firstRowStyle)->addText('Место нахождения юридического лица или место регистрации физического лица', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(2500, $firstRowStyle)->addText('Документ, удостоверяющий личность, или дата, № свидетельства о государственной регистрации', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1000, $firstRowStyle)->addText('Тип участника', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Дата приобретения (перехода доли)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1250, $firstRowStyle)->addText('Размер доли в уставном капитале общества (%)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1750, $firstRowStyle)->addText('Номинальная стоимость доли в уставном капитале общества (руб.)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Сведения об оплате доли (руб.)', $fontStyleTable, $paragraphStyleCenter);

        $count = 0;
        foreach ($document['СвЮЛ']['СвУчредит'] as $groupKey => $groupValue) { // проходимся по участникам
            foreach ($groupValue as $key => $value) { // проходимся по ключу участника
                if ($key == 'ГРНДатаПерв') { // проверка на вложенность массива второго уровня
                    if ($groupKey == 'УчрФЛ') { // проверка, что фл
                        $table->addRow();
                        $count++;
                        $table->addCell(500)->addText($count, $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(3000)->addText($groupValue['СвФЛ']['@attributes']['Фамилия'] . ' ' . $groupValue['СвФЛ']['@attributes']['Имя'] . ' ' . $groupValue['СвФЛ']['@attributes']['Отчество'], $fontStyleHeader, $paragraphStyleLeft);
                        $table->addCell(3000)->addText('Заполнить', $fontStyleRegular, $paragraphStyleLeft);
                        $table->addCell(2500)->addText('Заполнить', $fontStyleRegular, $paragraphStyleLeft);
                        $table->addCell(1000)->addText('Физ. лицо', $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1500)->addText(date("d.m.Y", strtotime($groupValue['ГРНДатаПерв']['@attributes']['ДатаЗаписи'])), $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1250)->addText($groupValue['ДоляУстКап']['РазмерДоли']['Процент'], $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1750)->addText($groupValue['ДоляУстКап']['@attributes']['НоминСтоим'], $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1500)->addText('Оплачена полностью', $fontStyleRegular, $paragraphStyleCenter);
                    } else if ($groupKey == 'УчрЮЛРос') {

                        $documentInForeach = $documentsService->getDocumentFromFNS($groupValue['НаимИННЮЛ']['@attributes']['ИНН']);

                        $table->addRow();
                        $count++;
                        $table->addCell(500)->addText($count, $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(3000)->addText($groupValue['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'], $fontStyleHeader, $paragraphStyleLeft);
                        $table->addCell(3000)->addText($this->createFullAddress($documentInForeach), $fontStyleRegular, $paragraphStyleLeft);
                        $table->addCell(2500)->addText('ОГРН: ' . $groupValue['НаимИННЮЛ']['@attributes']['ОГРН'], $fontStyleRegular, $paragraphStyleLeft);
                        $table->addCell(1000)->addText('Юр. лицо', $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1500)->addText(date("d.m.Y", strtotime($groupValue['ГРНДатаПерв']['@attributes']['ДатаЗаписи'])), $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1250)->addText($groupValue['ДоляУстКап']['РазмерДоли']['Процент'], $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1750)->addText($groupValue['ДоляУстКап']['@attributes']['НоминСтоим'], $fontStyleRegular, $paragraphStyleCenter);
                        $table->addCell(1500)->addText('Оплачена полностью', $fontStyleRegular, $paragraphStyleCenter);
                    }
                    continue;
                }
                if ($key == 'УчрФЛ') { // проверка, что фл
                    dd($value);
                    $table->addRow();
                    $count++;
                    $table->addCell(500)->addText($count, $fontStyleRegular, $paragraphStyleCenter);
                    $table->addCell(3000)->addText($value['СвФЛ']['@attributes']['Фамилия'] . ' ' . $value['СвФЛ']['@attributes']['Имя'] . ' ' . $value['СвФЛ']['@attributes']['Отчество'], $fontStyleHeader, $paragraphStyleLeft);
                    $table->addCell(3000)->addText('Заполнить', $fontStyleRegular, $paragraphStyleLeft);
                    $table->addCell(2500)->addText('Заполнить', $fontStyleRegular, $paragraphStyleLeft);
                    $table->addCell(1000)->addText('Физ. лицо', $fontStyleRegular, $paragraphStyleCenter);
                    $table->addCell(1500)->addText(date("d.m.Y", strtotime($value['ГРНДатаПерв']['@attributes']['ДатаЗаписи'])), $fontStyleRegular, $paragraphStyleCenter);
                    $table->addCell(1250)->addText($value['ДоляУстКап']['РазмерДоли']['Процент'], $fontStyleRegular, $paragraphStyleCenter);
                    $table->addCell(1750)->addText($value['ДоляУстКап']['@attributes']['НоминСтоим'], $fontStyleRegular, $paragraphStyleCenter);
                    $table->addCell(1500)->addText('Оплачена полностью', $fontStyleRegular, $paragraphStyleCenter);
                } else if ($groupKey == 'УчрЮЛРос') {

//                    $documentInForeach = $documentsService->getDocumentFromFNS($value['НаимИННЮЛ']['@attributes']['ИНН']);

                    $table->addRow();
                    $count++;
                    $table->addCell(500)->addText($count, $fontStyleRegular, $paragraphStyleCenter);
//                    $table->addCell(3000)->addText($value['НаимИННЮЛ']['@attributes']['НаимЮЛПолн'], $fontStyleHeader, $paragraphStyleLeft);
                    $table->addCell(3000)->addText($this->createFullAddress($documentInForeach), $fontStyleRegular, $paragraphStyleLeft);
//                    $table->addCell(2500)->addText('ОГРН: ' . $value['НаимИННЮЛ']['@attributes']['ОГРН'], $fontStyleRegular, $paragraphStyleLeft);
                    $table->addCell(1000)->addText('Юр. лицо', $fontStyleRegular, $paragraphStyleCenter);
//                    $table->addCell(1500)->addText(date("d.m.Y", strtotime($value['ГРНДатаПерв']['@attributes']['ДатаЗаписи'])), $fontStyleRegular, $paragraphStyleCenter);
//                    $table->addCell(1250)->addText($value['ДоляУстКап']['РазмерДоли']['Процент'], $fontStyleRegular, $paragraphStyleCenter);
//                    $table->addCell(1750)->addText($value['ДоляУстКап']['@attributes']['НоминСтоим'], $fontStyleRegular, $paragraphStyleCenter);
//                    $table->addCell(1500)->addText('Оплачена полностью', $fontStyleRegular, $paragraphStyleCenter);
                }
            }
        }


//        $section->addPageBreak();
        // Подраздел 1.2
        $section->addTextBreak();
        $section->addText('Подраздел 1.2. Сведения о долях, принадлежащих ' . $document['СвЮЛ']['СвНаимЮЛ']['СвНаимЮЛСокр']['@attributes']['НаимСокр'] . ' на ' . $this->dateWithMonthRu(), $fontStyleRegular, $paragraphStyleLeft);

        // Таблица сведений о долях
        $table = $section->addTable($tableStyle);

        // Заголовки таблицы сведений о долях
        $table->addRow();
        $table->addCell(500, $firstRowStyle)->addText('№ п/п', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(3000, $firstRowStyle)->addText('Полное фирменное наименование общества', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(3000, $firstRowStyle)->addText('Место нахождения общества', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(2500, $firstRowStyle)->addText('Дата, № свидетельства о государственной регистрации', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Тип участника', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Дата приобретения (перехода доли)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(1500, $firstRowStyle)->addText('Размер доли в уставном капитале общества (%)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(2000, $firstRowStyle)->addText('Номинальная стоимость доли в уставном капитале общества (руб.)', $fontStyleTable, $paragraphStyleCenter);
        $table->addCell(2000, $firstRowStyle)->addText('Сведения об оплате доли (руб.)', $fontStyleTable, $paragraphStyleCenter);

        dd($document);

        if (isset ($document['СвЮЛ']['СвДоляООО'])) {
            $table->addRow();
            $table->addCell(500)->addText('1', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(3000)->addText(, $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(3000)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(2500)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(1500)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(1500)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(1500)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(2000)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
            $table->addCell(2000)->addText('нет', $fontStyleRegular, $paragraphStyleCenter);
        }

        // Подпись
        $section->addTextBreak();
        $section->addText('Генеральный директор                                                                                                                                                                 Исаев О.К.', $fontStyleRegular, $paragraphStyleLeft);

        // Сохраняем документ
        $objWriter = IOFactory::createWriter($phpWord);

        try {
            $objWriter->save(storage_path('test.docx'));
        } catch (Exception $e) {}

        return response()->download(storage_path('test.docx'));

//        return redirect(route('dashboard'));
    }
}
