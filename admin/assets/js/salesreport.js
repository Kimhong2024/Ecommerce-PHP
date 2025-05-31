$(document).ready(function () {
    let salesChart;
  
    // Initialize Date Filters
    const today = new Date().toISOString().split('T')[0];
    $('#startDate').val(today);
    $('#endDate').val(today);
  
    // Generate Report
    $('#generateReportBtn').click(function () {
      generateReport();
    });
  
    // Export Report
    $('#exportReportBtn').click(function () {
      exportReport();
    });
  
    // Print Report
    $('#printReportBtn').click(function () {
      window.print();
    });
  
    // Generate Report Function
    function generateReport() {
      const reportType = $('#reportType').val();
      const startDate = $('#startDate').val();
      const endDate = $('#endDate').val();
      const groupBy = $('#groupBy').val();
  
      $.ajax({
        url: 'api/get_sales_report.php',
        method: 'GET',
        data: { reportType, startDate, endDate, groupBy },
        success: function (response) {
          const data = JSON.parse(response);
          renderSalesChart(data);
          renderSalesTable(data);
        },
        error: function (xhr, status, error) {
          alert('Error generating report: ' + error);
        }
      });
    }
  
    // Render Sales Chart
    function renderSalesChart(data) {
      const ctx = document.getElementById('salesChart').getContext('2d');
  
      if (salesChart) {
        salesChart.destroy();
      }
  
      salesChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Total Sales',
            data: data.sales,
            borderColor: '#36A2EB',
            fill: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return '$' + value;
                }
              }
            }
          }
        }
      });
    }
  
    // Render Sales Table
    function renderSalesTable(data) {
      let tableRows = '';
      data.tableData.forEach(row => {
        tableRows += `
          <tr>
            <td>${row.date}</td>
            <td>$${row.total_sales.toFixed(2)}</td>
            <td>${row.total_orders}</td>
            <td>$${row.average_order_value.toFixed(2)}</td>
          </tr>`;
      });
      $('#salesTable tbody').html(tableRows);
    }
  
    // Export Report Function
    function exportReport() {
      const table = $('#salesTable').clone();
      table.find('th, td').css('border', '1px solid black').css('padding', '5px');
      const html = `
        <html>
          <head>
            <style>
              table { border-collapse: collapse; width: 100%; }
              th, td { border: 1px solid black; padding: 5px; text-align: left; }
            </style>
          </head>
          <body>
            <h2>Sales Report</h2>
            ${table[0].outerHTML}
          </body>
        </html>`;
      const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'sales_report.xls';
      a.click();
    }
  });