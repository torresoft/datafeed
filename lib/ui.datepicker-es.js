/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood@virginbroadband.com.au) and Stéphane Nahmani (sholby@sholby.net). */
jQuery(function($){
	$.datepicker.regional['es'] = {clearText: 'Limpiar', clearStatus: '',
		closeText: 'Cerrar', closeStatus: 'Cerrar Calendario',
		prevText: '<ant', prevStatus: 'Mes Anterior',
		nextText: 'sig>', nextStatus: 'Mes Siguiente',
		currentText: 'Actual', currentStatus: 'Ir al actual',
		monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
		'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
		monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
		'Jul','Ago','Sep','Oct','Nov','Dic'],
		monthStatus: 'Ir a un mes', yearStatus: 'Ir a un año',
		weekHeader: 'Sm', weekStatus: '',
		dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
		dayNamesShort: ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'],
		dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
		dayStatus: 'Dia de la semana', dateStatus: 'Elija el dia',
		dateFormat: 'yy-mm-dd', firstDay: 0, 
		initStatus: 'Seleccione', isRTL: false, yearSuffix: ''};
		
	$.datepicker.setDefaults($.datepicker.regional['es']);
});