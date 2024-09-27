<?php
/*
Plugin Name: Investment Calculator
Plugin URI: https://yourwebsite.com/
Description: A plugin to calculate projected returns on an investment and display data on a chart.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com/
License: GPL2
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Function to create the investment calculator form and logic
function investment_calculator()
{
    ob_start(); // Start output buffering
?>
    <div style="display: flex; justify-content: space-between; padding: 20px;">
        <!-- Left Side: Sliders for inputs -->
        <div style="width: 45%; border: 1px solid #e5e5e5; padding: 20px; border-radius: 8px;">
            <h4>How much do you want to invest?</h4>
            <div class="lv"> 
                <label for="initial-investment" class="label">Initial Investment (KSH): </label>
                <span id="investment-value">50,000</span>
            </div>
            <input type="range" id="initial-investment" min="10000" max="1000000" step="5000" value="50000" oninput="updateInvestmentLabel()" class="range">
            
            &nbsp;

            <div class="lv">
                <label for="property-growth" class="label">Expected annual property growth: </label>
                <div><span id="property-growth-value">30</span>%</div>
            </div>
            <input type="range" id="property-growth" min="0" max="100" step="1" value="30" oninput="updatePropertyGrowthLabel()" class="range">

            &nbsp;

            <div class="lv">
                <label for="rental-yield" class="label">Expected annual rental yield: </label>
                <div><span id="rental-yield-value">10</span>%</div>
            </div>
            <input type="range" id="rental-yield" min="0" max="20" step="1" value="10" oninput="updateRentalYieldLabel()" class="range">

            &nbsp;

            <p style="font-size:small">All projected values are based on the inputs and assume a 5-year holding period.</p>
        </div>

        <!-- Right Side: Chart display -->
        <div style="width: 50%;">
            <h4>Projected investment return</h4>
            <p id="projected-returns"></p>
            <canvas id="investment-chart" width="600" height="300"></canvas>
        </div>
    </div>

    <style>

        .lv{
            display: flex;
            justify-content: space-between;
        }
        .label{
            font-size: 16px;
        }
        .range {
            -webkit-appearance: none;
            /* Chrome/Safari */
            -moz-appearance: none;
            /* Firefox */
            appearance: none;
            width: 100%;
            height: 5px;
            /* Height of the range track */
            border-radius: 5px;
            background: linear-gradient(to right, #2196F3 50%, #e5e5e5 50%);
            cursor: pointer;
        }

        /* WebKit Browsers (Chrome, Safari) */
        .range::-webkit-slider-thumb {
            -webkit-appearance: none;
            background: #ffffff !important;
            border: 6px solid #2196F3 !important;
            height: 15px;
            width: 15px;
            border-radius: 50%;
            cursor: pointer;
        }

        /* Firefox Browsers */
        .range::-moz-range-thumb {
            -moz-appearance: none;
            /* Required for Firefox */
            background: #ffffff !important;
            border: 6px solid #2196F3 !important;
            height: 10px !important;
            /* Adjust height */
            width: 10px !important;
            /* Adjust width */
            border-radius: 50%;
            cursor: pointer;
            
            /* Remove default border */
        }

        /* General for all browsers */
        .range::-webkit-slider-runnable-track{
            box-shadow: none !important;
        }

        .range::-moz-range-track {
            width: 100%;
            height: 5px;
            /* Adjust height of the track */
            border-radius: 5px;
            box-shadow: none !important;
            /* background: linear-gradient(to right, #2196F3 50%, #e5e5e5 50%); */
        }
    </style>

    <script>
        var investmentChart = null; // Global variable to hold the chart instance

        // Function to update labels when slider values change
        function updateInvestmentLabel() {
            var investment = document.getElementById('initial-investment').value;
            document.getElementById('investment-value').innerText = parseInt(investment).toLocaleString();
            updateRangeBackground('initial-investment');
            calculateReturns();
        }

        function updatePropertyGrowthLabel() {
            var growth = document.getElementById('property-growth').value;
            document.getElementById('property-growth-value').innerText = growth;
            updateRangeBackground('property-growth');
            calculateReturns();
        }

        function updateRentalYieldLabel() {
            var yield = document.getElementById('rental-yield').value;
            document.getElementById('rental-yield-value').innerText = yield;
            updateRangeBackground('rental-yield');
            calculateReturns();
        }

        // Function to update range slider background color
        function updateRangeBackground(rangeId) {
            var slider = document.getElementById(rangeId);
            var value = (slider.value - slider.min) / (slider.max - slider.min) * 100;
            slider.style.background = 'linear-gradient(to right, #2196F3 ' + value + '%, #EBEEF4 ' + value + '%)';
        }

        // Function to calculate and display projected returns
        function calculateReturns() {
            var investment = parseFloat(document.getElementById('initial-investment').value);
            var growthRate = parseFloat(document.getElementById('property-growth').value) / 100;
            var rentalYield = parseFloat(document.getElementById('rental-yield').value) / 100;

            // Assumed holding period (5 years)
            var holdingPeriod = 5;

            // Arrays to hold values for each year
            var initialInvestmentArray = [];
            var rentalIncomeArray = [];
            var appreciationArray = [];

            var totalRentalIncome = 0;

            for (var year = 1; year <= holdingPeriod; year++) {
                var appreciation = investment * growthRate; // Annual property appreciation
                totalRentalIncome += investment * rentalYield; // Cumulative rental income

                appreciationArray.push(appreciation); // Value appreciation for each year
                rentalIncomeArray.push(totalRentalIncome); // Cumulative rental income
                initialInvestmentArray.push(investment); // Initial investment remains constant
            }

            // Update projected returns for the 5th year
            document.getElementById('projected-returns').innerText = 'KSH ' + (investment + appreciationArray[4] + rentalIncomeArray[4]).toLocaleString() + ' in 5 years';

            // Update Chart
            displayChart(initialInvestmentArray, rentalIncomeArray, appreciationArray);
        }

        // Function to display chart and re-render it when inputs change
        function displayChart(initialInvestmentArray, rentalIncomeArray, appreciationArray) {
            var ctx = document.getElementById('investment-chart').getContext('2d');

            // Destroy previous chart if it exists to avoid overlap
            if (investmentChart) {
                investmentChart.destroy();
            }

            // Create new stacked chart
            investmentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5'],
                    datasets: [{
                            label: 'Initial Investment',
                            data: initialInvestmentArray,
                            backgroundColor: '#121C30',
                            borderRadius:5,
                            stack: 'Stack 0'
                        },
                        {
                            label: 'Cumulative Rental Income',
                            data: rentalIncomeArray,
                            backgroundColor: '#03498A',
                            borderRadius:5,
                            stack: 'Stack 0'
                        },
                        {
                            label: 'Annual Value Appreciation',
                            data: appreciationArray,
                            backgroundColor: '#2196F3',
                            borderRadius:5,
                            stack: 'Stack 0'
                        }
                    ]
                },
                options: {
                    scales: {
                        x: {
                            stacked: true, // Enable stacking on the X-axis
                        },
                        y: {
                            beginAtZero: true,
                            stacked: true, // Enable stacking on the Y-axis
                            title: {
                                display: true,
                                text: 'KSH'
                            }
                        }
                    },
                    responsive: true,
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    }
                }
            });
        }

        // Initial calculation when the page loads
        window.addEventListener('load', function() {
            calculateReturns(); // Calculate the returns initially
            updateRangeBackground('initial-investment');
            updateRangeBackground('property-growth');
            updateRangeBackground('rental-yield');
        });
    </script>


<?php
    return ob_get_clean(); // Return the buffered output
}

// Create a shortcode for the investment calculator
add_shortcode('investment_calculator', 'investment_calculator');

// Enqueue Chart.js library for the chart functionality
function load_chartjs()
{
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'load_chartjs');
