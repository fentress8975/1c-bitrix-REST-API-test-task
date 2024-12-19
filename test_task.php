<?php
//1 - для человеческого вывода, 0 - для рекомендованого задание
define('PRETTY_OUTPUT', 0, false);
//url api 
define('API_URL', 'change_me', false);
//идентификатор поля "Баллы"
define('FIELD_NAME_POINTS', 'change_me', false);
//идентификатор пользовательского типа смарт-процесса
define('FIELD_CUSTOM_USER_TYPE_ID', 'change_me', false);

//CURL функции
//Получение всех контактов.
function curlAllContacts(array $select = null, array $filter = null, array $order = null): Generator
{
    $page = 1;
    do {
        $get = array(
            'SELECT' => $select,
            'FILTER' => $filter,
            'ORDER' => $order,
            'start' => ($page - 1) * 50
        );
        $ch = curl_init(API_URL . '/crm.contact.list?' . http_build_query($get));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resContacts = json_decode(curl_exec($ch));
        curl_close($ch);
        $page++;
        yield $resContacts;
    } while ($resContacts->total >= ($page - 1) * 50);
}
//Получение всех сделок.
function curlAllDeals(array $select = null, array $filter = null, array $order = null): Generator
{
    $page = 1;
    do {
        $get = array(
            'SELECT' => $select,
            'FILTER' => $filter,
            'ORDER' => $order,
            'start' => ($page - 1) * 50
        );
        $ch = curl_init(API_URL . '/crm.deal.list?' . http_build_query($get));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resDeals = json_decode(curl_exec($ch));
        curl_close($ch);
        $page++;
        yield $resDeals;
    } while ($resDeals->total >= ($page - 1) * 50);
}
//получение всех элементов пользовательского смарт-процесса. 
function curlAllItemsByCustomType(int $entityTypeId, array $select = null, array $filter = null, array $order = null): Generator
{
    $page = 1;
    do {
        //В нижнем регистре, api только так работает
        $get = array(
            'entityTypeId' => $entityTypeId,
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
            'start' => ($page - 1) * 50
        );
        $ch = curl_init(API_URL . '/crm.item.list?' . http_build_query($get));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resItems = json_decode(curl_exec($ch));
        curl_close($ch);
        $page++;
        yield $resItems;
    } while ($resItems->total >= ($page - 1) * 50);
}
//конец CURL функций


//Подсчет всех контактов по полю total
function numberOfContacts(): int
{
    foreach (curlAllContacts(['id']) as $values) {
        return $values->total;
    }
}
//Подсчет всех сделок по полю total
function numberOfDeals(): int
{
    foreach (curlAllDeals(['id']) as $values) {
        return $values->total;
    }
}
//Подсчет контактов с заполненым полем комментарий(любое значение в строке) по полю total
function numberOfContactsWithComment(): int
{
    $result = 0;
    foreach (curlAllContacts(null, ['!=COMMENTS' => '']) as $values) {
        return $values->total;
    }
    return $result;
}
//Возвращает массив всех сделок без контактов
function allDealsWithoutContact(): array
{
    $res = array();
    foreach (curlAllDeals(null, ['CONTACT_ID' => 'null']) as $values) {
        foreach ($values->result as $key => $value) {
            $res[] = $value;
        }
    }
    return $res;
}
//Подсчет количество сделок в каждом направлении
function countDealsInHoppers(): array
{
    $hoppersArray = array();
    foreach (curlAllDeals(['CATEGORY_ID']) as $values) {
        foreach ($values->result as $key => $value) {
            array_key_exists($value->CATEGORY_ID, $hoppersArray) ? $hoppersArray[$value->CATEGORY_ID] += 1 : $hoppersArray[$value->CATEGORY_ID] = 1;
        }
    }
    return $hoppersArray;
}
//Подсчет суммы значений поля "Баллы" с id ufCrm5_1734072847 у всех существующих элементов Смарт процесса типа 1038.
function countPointsInItems(): int|float
{
    $sum = 0;
    foreach (curlAllItemsByCustomType(FIELD_CUSTOM_USER_TYPE_ID, [FIELD_NAME_POINTS], ['>=' . FIELD_NAME_POINTS => '1']) as $values) {
        foreach ($values->result->items as $key => $value) {
            $sum += $value->{FIELD_NAME_POINTS};
        }
    }
    return $sum;
}

//Main
if (PRETTY_OUTPUT === 0) {
    $result = array();

    $result["count_contacts"] = numberOfContacts();
    $result["count_deals"] = numberOfDeals();
    $result["count_with_comments"] = numberOfContactsWithComment();
    $result["deals_without_contacts"] = count(allDealsWithoutContact());
    $countDealsInEachHopper = countDealsInHoppers();
    foreach ($countDealsInEachHopper as $hopperId => $hopperValue) {
        $result["count_{$hopperId}_hopper"] = $hopperValue;
    }

    $result["points_sum"] = countPointsInItems();
    print_r($result);
} elseif (PRETTY_OUTPUT === 1) {

    echo "Количество контактов: " . numberOfContacts() . PHP_EOL;

    echo "Количество сделок: " . numberOfDeals() . PHP_EOL;

    echo "Количество контактов с заполненым комментарием: " . numberOfContactsWithComment() . PHP_EOL;

    echo "Cделки без контактов: " . PHP_EOL;
    print_r(allDealsWithoutContact()) . PHP_EOL;

    echo "Количество сделок в направлениях: " . PHP_EOL;
    $countDealsInEachHopper = countDealsInHoppers();
    foreach ($countDealsInEachHopper as $hopperId => $hopperValue) {
        echo  "\t Направление $hopperId имеет $hopperValue сделки(-ок)" . PHP_EOL;
    }
    echo "Cумма значений поля \"Баллы\": ";
    echo countPointsInItems(countPointsInItems()) . PHP_EOL;
}
