$(document).ready(function() { 
    
    $("#seedpoints_table").dataTable(
      {
      "sScrollY": "200px",
      "bFilter": true,
      "bPaginate": false,
      "bSort": true,
      "bInfo": true,
      "aaSorting": [ [0,'asc'], [1,'asc'] ]
      }
    );

    } );

