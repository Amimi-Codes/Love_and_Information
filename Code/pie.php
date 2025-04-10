<!-- pie.php -->
<?php
// Database connection
$db_config = [
    'host' => 'localhost',
    'dbname' => 'u822894334_AnswersDB',
    'user' => 'u822894334_LoveandInfo',
    'password' => 'Love&Info'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']}",
        $db_config['user'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get today's responses summed up
    $query = "SELECT 
                SUM(IE) as ie_total,
                SUM(DN) as dn_total,
                SUM(SO) as so_total
              FROM quiz_responses 
              WHERE DATE(date) = CURDATE()";
    
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Format data for Highcharts
    $pieData = [
        ['name' => 'Information Enthusiast', 'y' => (int)$result['ie_total']],
        ['name' => 'Digital Native', 'y' => (int)$result['dn_total']],
        ['name' => 'Human Connection', 'y' => (int)$result['so_total']]
    ];

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Love and Information - Response Analysis</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f5f5f5;
    }
    
    .title {
        text-align: center;
        margin-bottom: 15px; /* Reduced margin */
    }
    
    .date {
        text-align: center;
        margin-bottom: 20px; /* Reduced margin */
        font-style: italic;
        font-size: 1.1em;
    }
    
    #chartContainer {
        height: 500px;
        margin: 20px 0; /* Reduced margin */
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .traits-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px; /* Reduced margin */
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .traits-table th {
        background-color: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-size: 1.1em;
    }
    
    .traits-table td {
        padding: 15px;
        border-top: 1px solid #eee;
    }

    /* Trait column styling */
    .ie-row td:first-child {
        color: #6598cb;
        background-color: rgba(101, 152, 203, 0.1);
    }
    .dn-row td:first-child {
        color: #ef6596;
        background-color: rgba(239, 101, 150, 0.1);
    }
    .so-row td:first-child {
        color: #ebc041;
        background-color: rgba(235, 192, 65, 0.1);
    }
    
    @media print {
        body {
            padding: 0;
            background-color: white;
        }
        
        #chartContainer {
            box-shadow: none;
            padding: 0;
        }
        
        .traits-table {
            box-shadow: none;
        }

        .traits-table th {
            background-color: white;
            border-bottom: 2px solid #eee;
        }

        /* Ensure colors print properly */
        .ie-row td:first-child,
        .dn-row td:first-child,
        .so-row td:first-child {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
</head>
<body>
    <div class="title">
        <h1>Love and Information</h1>
        <h2>Questionnaire Responses</h2>
    </div>
    
    <div class="date">
        <?php echo date('F j, Y'); ?>
    </div>
    
    <div id="chartContainer"></div>
    
    <table class="traits-table">
        <tr>
            <th>Trait</th>
            <th>Description</th>
        </tr>
        <tr class="ie-row">
            <td><strong>Information Enthusiast</strong><br>(Information/Empirical)</td>
            <td>Measures how much the scene emphasizes analysis, research, and systematic understanding</td>
        </tr>
        <tr class="dn-row">
            <td><strong>Digital Native</strong><br>(Digital/Network)</td>
            <td>Measures technology, systems, patterns, and modern communication</td>
        </tr>
        <tr class="so-row">
            <td><strong>Sensory Observer</strong><br>(Social/Organic)</td>
            <td>Measures emotional weight, human relationships, and bodily experience</td>
        </tr>
    </table>

    <script>
        Highcharts.chart('chartContainer', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Trait Distribution'
            },
            credits: {
                enabled: false
            },
            colors: ['#6598cb', '#ef6596', '#ebc041'],
            tooltip: {
                pointFormat: '{ series.name}: <b>{point.percentage:.1f}%</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{ point.name}</b><br>{point.percentage:.1f}%',
                        distance: 20,
                        style: {
                            fontWeight: 'bold'
                        }
                    },
                    showInLegend: true
                }
            },
            legend: {
                align: 'right',
                verticalAlign: 'middle',
                layout: 'vertical'
            },
            series: [{
                name: 'Responses',
                colorByPoint: true,
                data: <?php echo json_encode($pieData); ?>
            }]
        });
    </script>
</body>
</html>