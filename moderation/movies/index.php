<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Фильмы");
?><?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	".default",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/moderation/include_area.php",
		"EDIT_TEMPLATE" => "standard.php"
	)
);?>
<pre>
1. Должна быть форма фильтрации фильма!
movie.moderation.filter.view.all - компонент
форма фильтрации должна распологаться на самом верху страницы и содержать значения по умолчанию для загружаемой страницы
Фильмы должны фильтровать:
- по названию (английскому названию) - значение по умолчанию - пусто
- год выпуска - значение по умолчанию - текущиц год
-- данный параметр под вопросом - прокат (сейчас в прокате, уже закончился (еще не было премьемы)) - только 2 значения. - значение по умолчанию - сейчас в прокате

2. Список фильмов, отфильтрованый по дате премьеры
особенности компонента:
- на странице по 20 записей
- формат вывода - | Порядковый номер записи в таблице [ID - фильма] | Название [ID - фильма] | Год выпуска фильма | Добавить постеры | Добавить видеотрейлеры ||||


3. 
Поля которые содержит фильм:
Название
Название на английском языке
Год выпуска фильма - 2012
Дата премьеры в мире - 31.05.2012
Дата премьеры в россии - 31.05.2012
Страна фильма - Россия, Украина (связано с инфоблоком - Страны)
Режиссер - Миха белых, Сергей (связано с инфоблоком - Персоны)
Актеры - Миха белых, Сергей (связано с инфоблоком - Персоны)
 
4. Ссылка "добавить фильм"

5. 

</pre>


<?$APPLICATION->IncludeComponent("extra:movie.moderation.filter.view.all", "ver1", array(
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "10",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>

<?$APPLICATION->IncludeComponent("extra:movie.moderation.view.all", "ver1", array(
	"IBLOCK_TYPE_MOVIE" => "kinoafisha",
	"IBLOCK_ID_MOVIE" => "17",
	"FILTER_NAME" => "arrFilter",
	"COUNT_ELEMENTS" => "30",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "8",
	),
	"MODER_GROUPS" => array(
		0 => "10",
	),
	"ADMIN_GROUPS" => array(
		0 => "10",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>






<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>