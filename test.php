<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />

  <!-- DATERANGE PICKER -->
  <!-- http://tamble.github.io/jquery-ui-daterangepicker/ -->
  <link href="util/jquery-ui/jquery-ui.min.css" 
	rel="stylesheet">
  <link href="util/daterangepicker/jquery.comiseo.daterangepicker.css" 
	rel="stylesheet">

  <script src="util/jquery-ui/jquery.min.js"></script>
  <script src="util/jquery-ui/jquery-ui.min.js"></script>
  <script src="util/jquery-ui/moment.min-locales.js"></script>

  <script src="util/daterangepicker/jquery.comiseo.daterangepicker.js"></script>
  
</head>
<body>
<form>
<input id="edate" name="range">
<div id="language"></div>
<script>
  $("#edate").daterangepicker({
      presetRanges: [{
          text: 'Hoy',
          dateStart: function() { return moment() },
          dateEnd: function() { return moment() }
      }, {
          text: 'Mañana',
          dateStart: function() { return moment().add('days', 1) },
          dateEnd: function() { return moment().add('days', 1) }
      }, {
          text: 'La próxima semana',
          dateStart: function() { return moment().add('weeks', 1).startOf('week') },
          dateEnd: function() { return moment().add('weeks', 1).endOf('week') }
      }],
      datepickerOptions: {
          minDate: 0,
          maxDate: null
      },
      applyOnMenuSelect: false,
      initialText : 'Seleccione el rango de fechas...',
      applyButtonText : 'Escoger',
      clearButtonText : 'Limpiar',
      cancelButtonText : 'Cancelar',
  });
  jQuery(function($){
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '&#x3c;Ant',
        nextText: 'Sig&#x3e;',
        currentText: 'Hoy',
        monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
                          'Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
        dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['es']);
  });
  $("#edate").daterangepicker({
      onOpen: $("#edate").daterangepicker(
          "setRange",
          {start:$.datepicker.parseDate("yy-mm-dd","2015-12-28"),
           end:$.datepicker.parseDate("yy-mm-dd","2015-12-31")}
      )
  });
</script>
<input type="submit" name="action" value="test">
</form>
<?php
echo "GET:<br/>";
print_r($_GET);
echo "<br/>";

?>
</body>
</html>
