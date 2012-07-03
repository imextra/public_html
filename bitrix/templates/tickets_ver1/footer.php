<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?> 

		</td>
		<?php
### Скрываем второй столбец, если находимся в разделе администрирования.... /moderation/
if (strpos($_SERVER['REQUEST_URI'], 'moderation') === false) {
	$hide = '';
} else {
	### Скрываем второй столбец
	$hide = 'hide';
}
		?>
		<td style="border:1px solid green; width:300px;" class=" <?=$hide?>">
			<div style="width:300px; height: 200px; background-color:#ccc; text-align:center; padding-top:30px; color:#444">Баннер 1</div>
		</td>
	</tr></table>
	</div>


	<div id="footer">
		<a href="/about">О проекте</a> / <a href="/faq">Вопросы и ответы</a> / <a href="/contacts">Контакты</a> / <a href="/blog">Блог</a>
		<div align="center" style="margin:10px 0 0 0"><?=rCopyRights(2012)?> Билеты через интернет.рф</div>
	</div>
</div>
<!--content-->

</body>
</html>