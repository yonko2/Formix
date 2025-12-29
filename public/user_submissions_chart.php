<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';
require_once '../logic/charts.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$days_range = isset($_GET['range']) && is_numeric($_GET['range']) ? (int)$_GET['range'] : 30;

$chart_data = get_user_submissions_chart_data($_SESSION['user_id'], $days_range);
?>
<?php include '../templates/header.php'; ?>

<div class="container chart-container">
    <div class="chart-header">
        <h2>My Form Submissions Over Time</h2>
        <div class="chart-actions">
            <a href="my_answers.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to My Answers
            </a>
            <div class="range-selector">
                <label for="range-select">Time Range:</label>
                <select id="range-select" class="form-select" onchange="changeRange(this.value)">
                    <option value="7" <?= $days_range == 7 ? 'selected' : '' ?>>Last 7 days</option>
                    <option value="30" <?= $days_range == 30 ? 'selected' : '' ?>>Last 30 days</option>
                    <option value="90" <?= $days_range == 90 ? 'selected' : '' ?>>Last 90 days</option>
                    <option value="180" <?= $days_range == 180 ? 'selected' : '' ?>>Last 180 days</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="chart-wrapper">
        <canvas id="submissionsChart"></canvas>
    </div>
    
    <?php if (array_sum($chart_data['data']) == 0) : ?>
        <div class="no-data-message">
            <p>No submissions found in the selected time period.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const chartData = <?= json_encode($chart_data) ?>;

const formattedLabels = chartData.labels.map(date => {
    const dateObj = new Date(date);
    return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('submissionsChart').getContext('2d');
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: formattedLabels,
            datasets: [{
                label: 'Form Submissions',
                data: chartData.data,
                backgroundColor: 'rgba(156, 104, 226, 0.6)',
                borderColor: '#9c68e2',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            const dateStr = chartData.labels[tooltipItems[0].dataIndex];
                            const date = new Date(dateStr);
                            return date.toLocaleDateString('en-US', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            });
                        }
                    }
                }
            }
        }
    });
});

function changeRange(range) {
    window.location.href = 'user_submissions_chart.php?range=' + range;
}
</script>

<?php include '../templates/footer.php'; ?> 
