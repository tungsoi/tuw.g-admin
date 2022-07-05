<style>
    .container {
        width: 100%;
        margin: 15px auto; 
        background: white;
    }

    .main {
        width: 100%;
        text-align: center;
    }
    .col-md-4 {
        float: left;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-10">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>

<script>
$(function () {
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($names, JSON_HEX_TAG) !!},
            datasets: [{
                label: 'Bieu do san luong van chuyen khach hang - ' + {!! json_encode($title, JSON_HEX_TAG) !!},
                data: {!! json_encode($value, JSON_HEX_TAG) !!},
                backgroundColor: {!! json_encode($color, JSON_HEX_TAG) !!},
                borderColor: {!! json_encode($color, JSON_HEX_TAG) !!},
                borderWidth: 1
            }]
        },
        options: {
            scaleShowValues: true,
            scales: {
                xAxes: [{
                ticks: {
                    autoSkip: false
                }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                }
            }
        }
    });
});
</script>