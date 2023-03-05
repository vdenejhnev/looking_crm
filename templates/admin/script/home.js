(function($) {

  $(document).ready(function() {
    const ctx = $('#chart')[0].getContext('2d');
    const chartColor="#FFFFFF";
    const gradientChartOptionsConfiguration = {
      maintainAspectRatio: false,
      legend: { display: false },
      tooltips: {
        bodySpacing: 4,
        mode: "nearest",
        intersect: 0,
        position: "nearest",
        xPadding: 10,
        yPadding: 10,
        caretPadding: 10
      },
      responsive:1,
      scales:{
        yAxes:[
          {
            display:0,
            gridLines:0,
            ticks:{display:false},
            gridLines:{
              zeroLineColor:"transparent",
              drawTicks:false,
              display:false,
              drawBorder:false
            }
          }
        ],
        xAxes:[
          {
            display:0,
            gridLines:0,
            ticks:{display:false},
            gridLines:{
              zeroLineColor:"transparent",
              drawTicks:false,
              display:false,
              drawBorder:false
            }
          }
        ]
      },
      layout:{
        padding:{
          left:0,
          right:0,
          top:15,
          bottom:15
        }
      }
    };
    const gradientChartOptionsConfigurationWithNumbersAndGrid={
      maintainAspectRatio:false,
      legend:{display:false},
      tooltips:{
        bodySpacing:4,
        mode:"nearest",
        intersect:0,
        position:"nearest",
        xPadding:10,
        yPadding:10,
        caretPadding:10
      },
      responsive:true,
      scales:{
        yAxes:[
          {
            gridLines:0,
            gridLines:{
              zeroLineColor:"transparent",
              drawBorder:false
            }
          }
        ],
        xAxes:[
          {
            display:0,
            gridLines:0,
            ticks:{display:false},
            gridLines:{
              zeroLineColor:"transparent",
              drawTicks:false,
              display:false,
              drawBorder:false
            }
          }
        ]
      },
      layout:{
        padding:{
          left:0,
          right:0,
          top:15,
          bottom:15
        }
      }
    };
    var gradientStroke=ctx.createLinearGradient(500,0,100,0);gradientStroke.addColorStop(0,'#80b6f4');gradientStroke.addColorStop(1,chartColor);var gradientFill=ctx.createLinearGradient(0,200,0,50);gradientFill.addColorStop(0,"rgba(128, 182, 244, 0)");gradientFill.addColorStop(1,"rgba(255, 255, 255, 0.24)");
    var chartData = CHART_DATA.map(item => item.count);
    //console.log(chartData);
    const chart = new Chart(ctx,{
      type:'line',
      data:{
        labels:["ЯНВ","ФЕВ", "МАР","АПР","МАЙ","ИЮН","ИЮЛ","АВГ","СЕН","ОКТ","НОЯ","ДЕК"],
        datasets:[
          {
            label:"Количество",
            borderColor:chartColor,
            pointBorderColor:chartColor,
            pointBackgroundColor:"#1e3d60",
            pointHoverBackgroundColor:"#1e3d60",
            pointHoverBorderColor:chartColor,
            pointBorderWidth:1,
            pointHoverRadius:7,
            pointHoverBorderWidth:2,
            pointRadius:5,
            fill:true,
            backgroundColor:gradientFill,
            borderWidth:2,
            data:chartData
          }
        ]
      },
      options:{
        layout:{padding:{left:30,right:20,top:72,bottom:50}},
        maintainAspectRatio:false,
        tooltips:{
          backgroundColor:'#fff',
          titleFontColor:'#333',
          bodyFontColor:'#666',
          bodySpacing:4,
          xPadding:12,
          mode:"nearest",
          intersect:0,
          position:"nearest"
        },
        legend:{
          position:"bottom",
          fillStyle:"#FFF",
          display:false
        },
        scales:{
          yAxes:[
            {
              ticks:{
                fontColor:"rgba(255,255,255,0.4)",
                fontStyle:"bold",
                beginAtZero:true,
                maxTicksLimit:5,
                padding:10
              },
              gridLines:{
                drawTicks:true,
                drawBorder:false,
                display:true,
                color:"rgba(255,255,255,0.1)",
                zeroLineColor:"transparent"
              }
            }
          ],
          xAxes:[
            {
              gridLines:{
                zeroLineColor:"transparent",
                display:false
              },
              ticks:{
                padding:10,
                fontColor:"rgba(255,255,255,0.4)",
                fontStyle:"bold"}
              }
            ]
          }
        }
      }
    ); 

    var panels = $('.panel');
    var months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    var curr_month = new Date().getMonth();
    var curr_year = new Date().getFullYear();
    var max_month = new Date().getMonth();

    function drawMonthLeads() {
      var item = 0;
      var month_count = 0;
      $.post(
        '/ajax/admin/home.php',
        {
          action: 'get_month_dealer_stat',
          year: $('.year-select').val(),
          month: curr_month + 1,
        },
        function(response) {
          $('.dealer-month-stat').children().empty();

          for (item = 0; item < response.length; item++) {
            month_count += response[item].leads_count;
            $('.dealer-month-stat').children().append('<tr><td>' + response[item].name + '</td><td>' + response[item].leads_count + '</td></tr>');
          }

          $('.month-stat-section .total').text(month_count);

          for (var item = 0; item < panels.length; item++) {
            var rows = $(panels[item]).find($('tr'));
        
            for (var row_item = 20; row_item < rows.length; row_item++) {
              $(rows[row_item]).addClass('hide-row');
              $(rows[row_item]).attr('data-panel', item);
            }
          }
        },
        'json'
      );

      $.post(
        '/ajax/admin/home.php',
        {
          action: 'get_month_company_stat',
          year: $('.year-select').val(),
          month: curr_month + 1,
        },
        function(response) {
          $('.company-month-stat').children().empty();

          for (item = 0; item < response.length; item++) {
            $('.company-month-stat').children().append('<tr><td>' + response[item].name + '</td><td>' + response[item].leads_count + '</td></tr>');
          }

          for (var item = 0; item < panels.length; item++) {
            var rows = $(panels[item]).find($('tr'));
        
            for (var row_item = 20; row_item < rows.length; row_item++) {
              $(rows[row_item]).addClass('hide-row');
              $(rows[row_item]).attr('data-panel', item);
            }
          }
        },
        'json'
      );
    }

    $('.month').text(months[curr_month]);
    drawMonthLeads();

   
    for (var item = 0; item < panels.length; item++) {
      var rows = $(panels[item]).find($('tr'));
  
      for (var row_item = 20; row_item < rows.length; row_item++) {
        $(rows[row_item]).addClass('hide-row');
        $(rows[row_item]).attr('data-panel', item);
      }
    }

    $('.show-panel-list').on('click', function() {
      var panel = $(this).attr('data-panel');
      var rows = $(panels[panel]).find($('.hide-row'));
      
      if ($(this).hasClass('show-panel-list')) {
          $(rows).css('display', 'table-row'); 
          $(this).removeClass('show-panel-list');  
          $(this).addClass('hide-panel-list');  
      } else {
          $(rows).css('display', 'none'); 
          $(this).removeClass('hide-panel-list');  
          $(this).addClass('show-panel-list'); 
      }     
    });

    $('.year-select').change(function(){
      curr_year = $(this).val();
      $.post(
        '/ajax/admin/home.php',
        {
          action: 'get_monthly_stat',
          year: $(this).val()
        },
        function(response) {
          chart.data.datasets[0].data = response;
          chart.update();
        },
        'json'
      );

      $.post(
        '/ajax/admin/home.php',
        {
          action: 'get_year_count',
          year: $(this).val()
        },
        function(response) {
          $('#leads-short-stat .total').text(response);
        },
        'json'
      );

      $.post(
        '/ajax/admin/home.php',
        {
          action: 'get_uniq_year_count',
          year: $(this).val()
        },
        function(response) {
          $('#leads-short-stat .uniq').text(response);
        },
        'json'
      );

      if ($(this).val() != (new Date().getFullYear())) {
        max_month = 11;
      } else {
        max_month = new Date().getMonth();
        curr_month = new Date().getMonth();
        $('.month').text(months[curr_month]);
      }

      drawMonthLeads();
    });    

    $('.month-prev').click(function() {
      if (curr_month == 0) {
        curr_month = max_month; 
      } else {
        curr_month-= 1;
      }
      $('.month').text(months[curr_month]);
      $('.month').data('month', curr_month);
    });

    $('.month-next').click(function() {
      if (curr_month == max_month) {
        curr_month = 0; 
      } else {
        curr_month+= 1;
      }
      $('.month').text(months[curr_month]);
    });

    $('.month-select-btn').click(function() {
      drawMonthLeads();
    });

    $('.full-stat').click(function() {
      $('.full-stat').attr('href', $('.full-stat').data('href') + '?year=' + curr_year + '&month=' + (curr_month + 1));
    }); 

    $('.months-dealers-stat').click(function() {
      $('.months-dealers-stat').attr('href', $('.months-dealers-stat').data('href') + '&year=' + curr_year + '&month=' + (curr_month + 1));
    });
    
    $('.months-companies-stat').click(function() {
      $('.months-companies-stat').attr('href', $('.months-companies-stat').data('href') + '&year=' + curr_year + '&month=' + (curr_month + 1));
    });      
      
  });

})(jQuery)

