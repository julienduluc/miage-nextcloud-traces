var baseUrl = OC.generateUrl('/apps/miageexemple');


$(document).ready(function () {

	function newFilter($filter){
		$.ajax({
			url: baseUrl + '/' + $filter,
			type: 'GET'
		}).done(function (response) {
			resData = response.response;
			//console.log(response.response);
			var data = [];

			resData.forEach(function(row) {
  				var obj = {};
  				obj.timestamp = row['timestamp'];
  				obj.user = row['user'];
  				obj.subject = row['subject'];
  				obj.affecteduser = row['affecteduser'];
  				obj.file = row['file'];
  				//console.log(obj);
  				data.push(obj);
			});
			//console.log(data);
			table.clear();
			table.rows.add(data).draw(false);

		}).fail(function (response, code) {
			console.log(response + ' ' + code);
		});
	}

	$('.filter').on('click',function () {
		newFilter($(this).attr('id'));
	});


    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var min = $('#min').datepicker("getDate");
            var max = $('#max').datepicker("getDate");
            var tDate = data[0].split(" ")[0].split('/');
            var tabDate = new Date(tDate[2], tDate[1] - 1, tDate[0]);
            if (min == null && max == null) { return true; }
            if (min == null && tabDate <= max) { return true; }
            if (max == null && tabDate >= min) { return true; }
            if (tabDate <= max && tabDate >= min) { return true; }
            return false;
        }
    );

    var dtMin = new Date(2000, 1 - 1, 1);
    var dtMax = new Date();

    $("#min").datepicker({ onSelect: function () { table.draw(); }, changeMonth: true, changeYear: true, dateFormat: 'dd/mm/yy', maxDate: dtMax, minDate: dtMin });
    $("#max").datepicker({ onSelect: function () { table.draw(); }, changeMonth: true, changeYear: true, dateFormat: 'dd/mm/yy', maxDate: dtMax, minDate: dtMin });

    var table = $('#datatable').DataTable({
    	"columns": [
            { "data": "timestamp" },
            { "data": "user" },
            { "data": "subject" },
            { "data": "affecteduser" },
            { "data": "file" }
        ],
        initComplete: function () {
            this.api().column(1).every(function () {
                var column = this;
                var select = $('<select class="user-filter"><option value=""></option></select>')
                    .appendTo($(column.footer()).empty())
                    .on('change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );

                        column.search(val ? '^' + val + '$' : '', true, false).draw();
                    });

                column.data().unique().sort().each(function (d, j) {
                    select.append('<option value="' + d + '">' + d + '</option>')
                });
            });
        },
        "order": [[ 0, "desc" ]]
    });

    $('#datatable tbody').on('mouseenter', 'td', function () {
        var colIdx = table.cell(this).index().column;
        $(table.cells().nodes()).removeClass('highlight');
        $(table.column(colIdx).nodes()).addClass('highlight');
    });

    // Event listener to the two range filtering inputs to redraw on input
    $('#min, #max').change(function () {
        table.draw();
    });
});