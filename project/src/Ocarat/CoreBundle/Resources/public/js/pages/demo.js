/*
 *  Document   : compCharts.js
 *  Author     : pixelcave
 *  Description: Custom javascript code used in Charts page
 */

/*
 *  Document   : uiTables.js
 *  Author     : pixelcave
 *  Description: Custom javascript code used in Tables page
 */

var demo = function() {

    // Get random number function from a given range
    var getRandomInt = function(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    };

    return {
        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            App.datatables();

            /* Initialize Datatables */
            $('#example-datatable').dataTable({
                "aoColumnDefs": [ {  } ],
                "iDisplayLength": 100,
                "aLengthMenu": [[5, 10, 20, 100], [5, 10, 20, 100]]
            });

            /* Add placeholder attribute to the search input */
            $('.dataTables_filter input').attr('placeholder', 'Rechercher');

            /* Select/Deselect all checkboxes in tables */
            $('thead input:checkbox').click(function() {
                var checkedStatus   = $(this).prop('checked');
                var table           = $(this).closest('table');

                $('tbody input:checkbox', table).each(function() {
                    $(this).prop('checked', checkedStatus);
                });
            });

        }
    };
}();