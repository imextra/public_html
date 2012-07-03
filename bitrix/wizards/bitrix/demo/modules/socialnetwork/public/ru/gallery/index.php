<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Фотогалерея");
?><p>Фотогалерея клуба. Ссылки с фотогалереи ведут в персональные галереи членов клуба.</p>

<?$APPLICATION->IncludeComponent("bitrix:photogallery_user", ".default", array(
	"SECTION_PAGE_ELEMENTS" => "10",
	"ELEMENTS_PAGE_ELEMENTS" => "100",
	"PAGE_NAVIGATION_TEMPLATE" => "",
	"ELEMENTS_USE_DESC_PAGE" => "Y",
	"IBLOCK_TYPE" => "gallery",
	"IBLOCK_ID" => "#IBLOCK_ID_GALLERY#",
	"GALLERY_GROUPS" => array(
	),
	"ONLY_ONE_GALLERY" => "Y",
	"SECTION_SORT_BY" => "ID",
	"SECTION_SORT_ORD" => "ASC",
	"ELEMENT_SORT_FIELD" => "id",
	"ELEMENT_SORT_ORDER" => "desc",
	"ANALIZE_SOCNET_PERMISSION" => "N",
	"UPLOAD_MAX_FILE_SIZE" => "2M",
	"GALLERY_AVATAR_SIZE" => "50",
	"ALBUM_PHOTO_THUMBS_SIZE" => "100",
	"ALBUM_PHOTO_SIZE" => "100",
	"THUMBNAIL_SIZE" => "90",
	"PREVIEW_SIZE" => "500",
	"JPEG_QUALITY1" => "95",
	"JPEG_QUALITY2" => "95",
	"JPEG_QUALITY" => "90",
	"WATERMARK_MIN_PICTURE_SIZE" => "200",
	"ADDITIONAL_SIGHTS" => array(
	),
	"UPLOAD_MAX_FILE" => "2",
	"PATH_TO_FONT" => "",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/club/gallery/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"DATE_TIME_FORMAT_SECTION" => "d.m.Y",
	"DATE_TIME_FORMAT_DETAIL" => "d.m.Y",
	"DISPLAY_PANEL" => "N",
	"SET_TITLE" => "Y",
	"USE_RATING" => "Y",
	"MAX_VOTE" => "5",
	"VOTE_NAMES" => array(
		0 => "0",
		1 => "1",
		2 => "2",
		3 => "3",
		4 => "4",
		5 => "",
	),
	"SHOW_TAGS" => "Y",
	"TAGS_PAGE_ELEMENTS" => "50",
	"TAGS_PERIOD" => "",
	"TAGS_INHERIT" => "Y",
	"TAGS_FONT_MAX" => "30",
	"TAGS_FONT_MIN" => "14",
	"TAGS_COLOR_NEW" => "486DAA",
	"TAGS_COLOR_OLD" => "486DAA",
	"TAGS_SHOW_CHAIN" => "Y",
	"USE_COMMENTS" => "Y",
	"COMMENTS_TYPE" => "forum",
	"FORUM_ID" => "#FORUM_ID#",
	"PATH_TO_SMILE" => "/bitrix/images/forum/smile/",
	"URL_TEMPLATES_READ" => "",
	"USE_CAPTCHA" => "Y",
	"SHOW_LINK_TO_FORUM" => "Y",
	"PREORDER" => "Y",
	"MODERATE" => "Y",
	"SHOW_ONLY_PUBLIC" => "Y",
	"WATERMARK_COLORS" => array(
		0 => "FF0000",
		1 => "FFFF00",
		2 => "FFFFFF",
		3 => "000000",
		4 => "",
	),
	"TEMPLATE_LIST" => ".default",
	"CELL_COUNT" => "0",
	"SLIDER_COUNT_CELL" => "4",
	"bxpiwidth" => "691",
	"SEF_URL_TEMPLATES" => array(
		"index" => "index.php",
		"galleries" => "galleries/#USER_ID#/",
		"gallery" => "/club/user/#USER_ID#/photo/",
		"gallery_edit" => "#USER_ALIAS#/action/#ACTION#/",
		"section" => "/club/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/",
		"section_edit" => "#USER_ALIAS#/#SECTION_ID#/action/#ACTION#/",
		"section_edit_icon" => "#USER_ALIAS#/#SECTION_ID#/icon/action/#ACTION#/",
		"upload" => "/club/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/action/upload/",
		"detail" => "/club/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/",
		"detail_edit" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
		"detail_slide_show" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
		"detail_list" => "list/",
		"search" => "search/",
		"tags" => "tags/",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>