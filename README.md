# Тестовое задание для Vigrom Corp #

## Требования ##

### Реализовать методы API для работы с кошельком пользователя. Ограничения: ###

* У пользователя может быть только один кошелек
* Поддерживаемые валюты: USD и RUB
* При вызове метода для изменения кошелька на сумму с отличной валютой от валюты кошелька, сумма должна конвертироваться по курсу
* Курсы обновляются переодически
* Все изменения кошелька должны фиксироваться в БД.

## Метод для изменения баланса ##

### Обязательные параметры метода: ###

* ID кошелька
* Тип транзакции (debit или credit)
* Сумма, на которую нужно изменить баланс
* Валюта суммы (допустимы значения: USD, RUB)
* Причина изменения счета (например: stock, refund). Список причин фиксирован

## Метод для изменения баланса ##

### Обязательные параметры метода: ###

* ID кошелька

## SQL запрос ##

Написать SQL запрос, который вернет сумму, полученную по причине refund за последние 7 дней


## Установка проекта 

Клонировать репозиторий на свой компьютер с помощью команды 
```
$ git clone
```
Настройить подключение к базе данных в файле .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={database_name}
DB_USERNAME={your_username}
DB_PASSWORD={your_password}
```

Затем выполнить в консносли следующие команды
```sh
$ composer install
$ php artisan migrate
$ php artisan passport:instal
$ php artisan passport:client --personal --name=authToken
$ php artisan key:generate
$ php artisan db:seed
```

И все готово!
