# Задача:

- Узнать количество контактов с заполненным полем COMMENTS
- Найти все сделки без контактов
- Узнать сколько сделок в каждой из существующих Направлений
- Посчитать сумму значений поля "Баллы" (предварительно узнав его код) из всех существующих элементов Смарт процесса

# Описание

Для работы скрипта нужно заполнить значения глобальных переменных:

- url_api - `define('API_URL', error, false)`
- id смарт-процесса - `define('FIELD_NAME_POINTS', error, false)`
- id пользовательского поля - `define('FIELD_CUSTOM_USER_TYPE_ID', error, false)`

| Функция | Описание |
| ----------- | ----------- |
| CURL|
| curlAllContacts(array $select = null, array $filter = null, array $order = null): Generator | Возвращает все контакты. Принимает стандартные параметры api(select,filter,order), возвращает генератор проходящий по всему списку от начала до конца. |
| curlAllDeals(array $select = null, array $filter = null, array $order = null): Generator | Возвращает все сделки. Принимает стандартные параметры api(select,filter,order), возвращает генератор проходящий по всему списку от начала до конца. |
| curlAllItemsByCustomType(int $entityTypeId, array $select = null, array $filter = null, array $order = null): Generator | Возвращает все элементы смарт-процесса. Принимает стандартные параметры api(select,filter,order), возвращает генератор проходящий по всему списку от начала до конца. |
||
||
| numberOfContacts(): int | Подсчет всех контактов по полю total. |
| numberOfDeals(): int | Подсчет всех сделок по полю total. |
| numberOfContactsWithComment(): int | Подсчет контактов с заполненным полем комментарий(любое значение в строке) по полю total. Использует фильтр `'!=COMMENTS' => ''`.|
| allDealsWithoutContact(): array | Возвращает массив всех сделок без контактов. |
|countDealsInHoppers(): array|Подсчет количество сделок в каждом направлении. Возвращает массив 'Id направления' => 'Количество'.|
|countPointsInItems(): int\float|Подсчет суммы значений поля "Баллы" с id из глобальной `'FIELD_NAME_POINTS'` у всех существующих элементов Смарт процесса из глобальной `'FIELD_CUSTOM_USER_TYPE_ID'`.|
