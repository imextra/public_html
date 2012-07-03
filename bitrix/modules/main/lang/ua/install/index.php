<?
$MESS["MF_EVENT_DESCRIPTION"] = "#AUTHOR# — Автор повідомлення
#AUTHOR_EMAIL# — E-mail автора повідомлення
#TEXT# — Текст повідомлення
#EMAIL_FROM# — E-mail відправника листа
#EMAIL_TO# — E-mail одержувача листа";
$MESS["MAIN_USER_INVITE_TYPE_DESC"] = "#ID# — ID користувача
#LOGIN# — Логін
#URL_LOGIN# — Логин, закодований для використання в URL
#EMAIL# — E-mail
#NAME# — Ім'я
#LAST_NAME# — Прізвище
#PASSWORD# — Пароль користувача 
#CHECKWORD# — Контрольний рядок для зміни паролю
#XML_ID# — ID користувача для зв'язку з зовнішніми джерелами
";
$MESS["MAIN_USER_INVITE_EVENT_NAME"] = "#SITE_NAME#: Запрошення на сайт";
$MESS["MAIN_NEW_USER_EVENT_NAME"] = "#SITE_NAME#: Зареєструвався новий користувач";
$MESS["MAIN_NEW_USER_CONFIRM_EVENT_NAME"] = "#SITE_NAME#: Підтвердження реєстрації нового користувача";
$MESS["MF_EVENT_SUBJECT"] = "#SITE_NAME#: Повідомлення з форми зворотного зв'язку";
$MESS["MAIN_USER_INFO_EVENT_NAME"] = "#SITE_NAME#: Реєстраційна інформація";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_DATE"] = "DD.MM.YYYY";
$MESS["MAIN_DEFAULT_SITE_FORMAT_DATE"] = "DD.MM.YYYY";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_DATETIME"] = "DD.MM.YYYY HH:MI:SS";
$MESS["MAIN_DEFAULT_SITE_FORMAT_DATETIME"] = "DD.MM.YYYY HH:MI:SS";
$MESS["MAIN_DEFAULT_LANGUAGE_NAME"] = "Ukrainian";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_CHARSET"] = "windows-1251";
$MESS["MAIN_DEFAULT_SITE_FORMAT_CHARSET"] = "windows-1251";
$MESS["MAIN_ADMIN_GROUP_NAME"] = "Адміністратори";
$MESS["MF_EVENT_NAME"] = "Відправлення повідомлення через форму зворотного зв'язку";
$MESS["MAIN_EVERYONE_GROUP_NAME"] = "Всі користувачі (у тому числі неавторизовані)";
$MESS["MAIN_EVERYONE_GROUP_DESC"] = "Всі користувачі, включаючи неавторизованих.";
$MESS["MAIN_MODULE_NAME"] = "Головний модуль";
$MESS["MAIN_USER_INVITE_TYPE_NAME"] = "Запрошення на сайт нового користувача";
$MESS["MAIN_NEW_USER_TYPE_NAME"] = "Зареєструвався новий користувач";
$MESS["MF_EVENT_MESSAGE"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Вам було відправлено повідомлення через форму зворотного зв'язку

Автор: #AUTHOR#
E-mail автора: #AUTHOR_EMAIL#

Текст повідомлення:
#TEXT#

Повідомлення створено автоматично.";
$MESS["MAIN_NEW_USER_CONFIRM_EVENT_DESC"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

Добридень,

Ви отримали це повідомлення, оскільки ваша адреса була використана при реєстрації нового користувача на сервері #SERVER_NAME#.

Ваш код для підтвердження реєстрації: #CONFIRM_CODE#

Для підтвердження реєстрації перейдіть за наступним посиланням:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#&confirm_code=#CONFIRM_CODE#

Ви також можете ввести код для підтвердження реєстрації на сторінці:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#

Увага! Ваш бюджет не буде активним, поки ви не підтвердите свою реєстрацію.

---------------------------------------------------------------------

Повідомлення сгенеровано автоматично.";
$MESS["MAIN_NEW_USER_EVENT_DESC"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------

На сайті #SERVER_NAME# успішно зареєстрований новий користувач.

Дані користувача:
ID користувача: #USER_ID#

Ім'я: #NAME#
Прізвище: #LAST_NAME#
E-mail: #EMAIL#

Login: #LOGIN#

Лист згенеровано автоматично.";
$MESS["MAIN_USER_INFO_EVENT_DESC"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------
#NAME# #LAST_NAME#,

#MESSAGE#

Ваша реєстраційна інформація:

ID користувача: #USER_ID#
Статус бюджету: #STATUS#
Login: #LOGIN#

Для зміни пароля перейдіть за наступним посиланням:
http://#SERVER_NAME#/bitrix/admin/index.php?change_password=yes&lang=ru&USER_CHECKWORD=#CHECKWORD#

Повідомлення сгенеровано автоматично.";
$MESS["MAIN_USER_INVITE_EVENT_DESC"] = "Інформаційне повідомлення сайту #SITE_NAME#
------------------------------------------
Добридень, #NAME# #LAST_NAME#!

Адміністратором сайту ви додані до числа зареєстрованих користувачів.

Запрошуємо Вас на наш сайт.

Ваша реєстраційна інформація:

ID користувача: #ID#
Login: #LOGIN#

Рекомендуємо вам змінити встановлений автоматично пароль.

Для зміни пароля перейдіть за наступним посиланням:
http://#SERVER_NAME#/auth.php?change_password=yes&USER_LOGIN=#URL_LOGIN#&USER_CHECKWORD=#CHECKWORD#
";
$MESS["MAIN_USER_INFO_TYPE_NAME"] = "Інформація про користувача";
$MESS["MAIN_INSTALL_DB_ERROR"] = "Не можу з'єднатися з базою даних. Перевірте правильність введених параметрів";
$MESS["MAIN_NEW_USER_CONFIRM_TYPE_NAME"] = "Підтвердження реєстрації нового користувача";
$MESS["MAIN_ADMIN_GROUP_DESC"] = "Повний доступ до управління сайтом.";
$MESS["MAIN_DEFAULT_SITE_NAME"] = "Сайт за умовчанням";
$MESS["MAIN_MODULE_DESC"] = "Ядро системи";
$MESS["MAIN_NEW_USER_TYPE_DESC"] = "

#USER_ID# — ID користувача
#LOGIN# — Логін
#EMAIL# — E-mail
#NAME# — Ім'я
#LAST_NAME# — Прізвище
#USER_IP# — IP користувача
#USER_HOST# — Хост користувача";
$MESS["MAIN_USER_INFO_TYPE_DESC"] = "

#USER_ID# — ID користувача
#STATUS# — Статус логіна
#MESSAGE# — Повідомлення користувачеві
#LOGIN# — Логін
#CHECKWORD# — Контрольний рядок для зміни паролю
#NAME# — Ім'я
#LAST_NAME# — Прізвище
#EMAIL# — E-mail користувача
";
$MESS["MAIN_NEW_USER_CONFIRM_TYPE_DESC"] = "


#USER_ID# — ID користувача
#LOGIN# — Логін
#EMAIL# — E-mail
#NAME# — Ім'я
#LAST_NAME# — Прізвище
#USER_IP# — IP користувача
#USER_HOST# — Хост користувача
#CONFIRM_CODE# — Код підтвердження
";
?>