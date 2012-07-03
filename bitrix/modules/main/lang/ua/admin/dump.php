<?
$MESS["MAIN_DUMP_FILE_DUMP_BUTTON"] = "Архівувати";
$MESS["MAIN_DUMP_BASE_TRUE"] = "Архівувати базу даних:";
$MESS["MAIN_DUMP_FILE_PUBLIC"] = "Архівувати публічну частину:";
$MESS["MAIN_DUMP_FILE_KERNEL"] = "Архівувати ядро:";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_b"] = "б ";
$MESS["MAIN_DUMP_BASE_TITLE"] = "База даних";
$MESS["MODE_SLOW"] = "Безпечний режим (якщо інші режими не працюють: без стиснення, з перервами між кроками)";
$MESS["MODE_DESC"] = "Буде створено повний архів публічної частини <b>поточного сайту</b> (для багатосайтової конфігурації на різних доменах), <b>ядра продукта</b> та <b>бази даних</b> (тілько для MySQL), який підходить для повного відновлення системи і перенесення на інший сервер. Після вибору одного з режимів можна скорегувати налаштування на вкладці &quot;<b>Розширені</b>&quot;.";
$MESS["MAIN_DUMP_ALERT_DELETE"] = "Ви впевнені, що бажаєте видалити файл?";
$MESS["MAIN_DUMP_DELETE"] = "Видалити";
$MESS["MODE_VPS"] = "Виділений сервер або VPS (оптимально за часом)";
$MESS["MAIN_DUMP_MASK"] = "Виключити з архіву файли і директорії за маскою:";
$MESS["MAIN_DUMP_FILE_MAX_SIZE"] = "Виключити з архіву файли розміром більше (0 — без обмеження):";
$MESS["MAIN_DUMP_BASE_IGNORE"] = "Виключити з архіву:";
$MESS["DISABLE_GZIP"] = "Вимкнути компресію архіву (зниження навантаження на процесор):";
$MESS["TIME_SPENT"] = "Витрачено часу:";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_gb"] = "Гб ";
$MESS["TIME_H"] = "год.";
$MESS["MAIN_DUMP_FOOTER_MASK"] = "Для маски виключення діють наступні правила:
 <p>
 <li>шаблон маски може містити символи &quot;*&quot;, які відповідають будь-якої кількості будь-яких символів в імені файла або папки;</li>
 <li> якщо на початку стоїть слеш (&quot;/&quot; или &quot;\\&quot;) шлях рахується від кореня сайту;</li>
 <li>у протилежному випадку шаблон застосовується до кожного файлу або папки;</li>
 <p>Приклади шаблонів:</p>
 <li>/content/photo — виключити папку повністю /content/photo;</li>
 <li>*.zip — виключити файли з розширенням &quot;zip&quot;;</li>
 <li>.access.php — виключити всі файли &quot;.access.php&quot;;</li>
 <li>/files/download/*.zip — виключити файли з розширенням &quot;zip&quot; у директорії /files/download;</li>
 <li>/files/d*/*.ht* — виключити файли з директорій, що починаються на  &quot;/files/d&quot;  з розширеннями, що починаються на &quot;ht&quot;.</li>
";
$MESS["MAIN_DUMP_HEADER_MSG"] = "Для перенесення архіву сайта на інший хостинг помістіть в кореневій папці нового сайта скрипт для відновлення <a href='/bitrix/admin/restore_export.php'>restore.php</a> і сам архів, потім наберіть у рядку браузера &quot;&lt;ім'я сайту&gt;/restore.php&quot; і дотримуйтесь інструкцій із розпакування.<br>Докладна інструкція доступна у <a href='http://dev.1c-bitrix.ru/api_help/main/going_remote.php' target=_blank>розділі справки</a>.";
$MESS["MAIN_DUMP_FILE_TIMESTAMP"] = "Змінений";
$MESS["MAIN_DUMP_FILE_STOP_BUTTON"] = "Зупинити";
$MESS["MAIN_DUMP_FILE_NAME"] = "Ім'я";
$MESS["MAIN_DUMP_FILE_STEP_SLEEP"] = "інтервал:";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_kb"] = "кб ";
$MESS["MAIN_DUMP_TAB"] = "Копіювання";
$MESS["MAIN_DUMP_FILE_STEP"] = "Крок:";
$MESS["MAIN_DUMP_BASE_SIZE"] = "МБ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_mb"] = "Мб ";
$MESS["BACKUP_NO_PERMS"] = "Немає прав на запис у папку /bitrix/backup";
$MESS["DUMP_NO_PERMS"] = "Немає прав на сервері на створення архіву";
$MESS["MAIN_DUMP_TABLE_FINISH"] = "Оброблено таблиць:";
$MESS["INTEGRITY_CHECK_OPTION"] = "Перевірити цілісність архіву після завершення:";
$MESS["INTEGRITY_CHECK"] = "Перевірка цілісності";
$MESS["MAIN_DUMP_ERROR"] = "Помилка";
$MESS["DUMP_NO_PERMS_READ"] = "Помилка відкриття архіву на читання";
$MESS["CDIR_FOLDER_OPEN_ERROR"] = "Помилка відкриття папки:";
$MESS["CDIR_FOLDER_ERROR"] = "Помилка обробки папки:";
$MESS["CDIR_FILE_ERROR"] = "Помилка обробки файлу:";
$MESS["CURRENT_POS"] = "Поточна позиція:";
$MESS["DUMP_CUR_PATH"] = "Поточний шлях:";
$MESS["MAIN_DUMP_BASE_SINDEX"] = "пошуковий індекс";
$MESS["MAIN_DUMP_SKIP_SYMLINKS"] = "Пропускати символічні посилання на директорії:";
$MESS["PUBLIC_PART"] = "Публічна частина сайту:";
$MESS["MAIN_DUMP_PAGE_TITLE"] = "Резервне копіювання";
$MESS["MAIN_DUMP_FILE_PAGES"] = "Резервні копії";
$MESS["MAIN_DUMP_ARC_SIZE"] = "Розмір архіву:";
$MESS["MAIN_DUMP_FILE_SIZE"] = "Розмір файлів:";
$MESS["FILE_SIZE"] = "Розмір файлу";
$MESS["MAIN_DUMP_RESTORE"] = "Розпакувати";
$MESS["TAB_ADVANCED"] = "Розширені";
$MESS["MAIN_DUMP_FILE_STEP_sec"] = "сек.";
$MESS["TIME_S"] = "сек.";
$MESS["SERVER_LIMIT"] = "Серверні обмеження";
$MESS["MAIN_DUMP_MYSQL_ONLY"] = "Система резервного копіювання працює тільки з базою даних MySQL.<br> Будь ласка, використовуйте зовнішні інструменти для створення архіву бази даних.";
$MESS["MAIN_DUMP_ACTION_DOWNLOAD"] = "Скачати";
$MESS["TAB_ADVANCED_DESC"] = "Спеціальні налаштування створення резервної копії";
$MESS["MODE_SHARED"] = "Стандартний хостинг (підходить для більшості сайтів)";
$MESS["TAB_STANDARD"] = "Стандартні";
$MESS["TAB_STANDARD_DESC"] = "Стандартні режими створення резервної копії";
$MESS["MAIN_DUMP_BASE_STAT"] = "статистику";
$MESS["DUMP_DB_CREATE"] = "Створення дампа бази даних";
$MESS["MAIN_DUMP_FILE_FINISH"] = "Створення резервної копії завершено";
$MESS["MAIN_DUMP_SITE_PROC"] = "Стискання...";
$MESS["MAIN_DUMP_DB_PROC"] = "Стиснення дампа бази даних";
$MESS["STEP_LIMIT"] = "Тривалість кроку:";
$MESS["MAIN_DUMP_ENCODE"] = "Увага! Ви використовуєте закодовану версію продукту";
$MESS["MAIN_RIGHT_CONFIRM_EXECUTE"] = "Увага! Розпакування резервної копії на діючому сайті може призвести до пошкодження сайта! Продовжити?";
$MESS["MAIN_DUMP_FILE_TITLE"] = "Файли";
$MESS["MAIN_DUMP_FILE_CNT"] = "Файлов стиснуто:";
$MESS["TIME_M"] = "хв.";
$MESS["MAIN_DUMP_MORE"] = "Ще…";
?>