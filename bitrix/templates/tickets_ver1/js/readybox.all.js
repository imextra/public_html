$(function() {	
	$("input:submit, input:button, a#addCinemaButton").button();

			
	
	var PREVIEW_PICTURE_CINEMA_MAX_FILESIZE = (1024)*100; // 100KB
	$('#PREVIEW_PICTURE_CINEMA').bind('change', function() {
		if(PREVIEW_PICTURE_CINEMA_MAX_FILESIZE < this.files[0].size){
			$('#PREVIEW_PICTURE_CINEMA').val('');
			showDialog('Ошибка ввода', 'Размер файла слишком большой!',300, 'error');
		}
	});	
	
	var PREVIEW_PICTURE_MOVIE_MAX_FILESIZE = (1024)*100; // 100KB
	$('#PREVIEW_PICTURE_MOVIE').bind('change', function() {
		if(PREVIEW_PICTURE_MOVIE_MAX_FILESIZE < this.files[0].size){
			$('#PREVIEW_PICTURE_MOVIE').val('');
			showDialog('Ошибка ввода', 'Размер файла слишком большой!',300, 'error');
		}
	});	
	
	$('.CINEMA_EDIT_BACK_TO_ALL').click(function (){
		var url = "/moderation/cinema/";
		$(location).attr('href',url);
	});
	
	$('.MOVIE_EDIT_BACK_TO_ALL').click(function (){
		var url = "/moderation/movies/";
		$(location).attr('href',url);
	});
	
	
});
