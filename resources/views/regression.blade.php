@extends('layouts.master')
@section('content')
    <div class="wrap-container">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <h1 class="m-0 text-dark">CO2 emissions</h1>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="col-sm-12 row group-select">
                <div class="col-sm-3">
                    <div class="small-title text-left">Display Year To</div>
                    <select class="form-control select-year-display">
                        @foreach($arrayYear as $key => $year)
                            @if($year !== last($arrayYear))
                            <option value="{{$key}}">{{$year}}</option>
                            @else
                                <option selected value="{{$key}}">{{$year}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <div class="small-title text-left">Display Regression</div>
                    <select class="form-control select-display-regression" name="display_regression">
                        <option value="normal">Chart Normal</option>
                        <option value="withRegression">Chart With Regression</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <div class="small-title text-left">Estimation CO2 in year</div>
                    <input type="number" id="year" placeholder="Enter the year" class="form-control">
                </div>
                <div class="col-sm-3">
                    <div class="row group-button-nav">
                        <a class="btn btn-info" id="estimation_by_time" href="javascript:;">Estimation</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 row group-select">
                <div class="col-sm-6">
                    <div class="text-left regression-information">
                        <div class="label-mse"><a target="_blank" href="https://vi.wikipedia.org/wiki/Sai_s%E1%BB%91_to%C3%A0n_ph%C6%B0%C6%A1ng_trung_b%C3%ACnh">MSE = </a></div>
                        <div class="value-mse"></div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="text-left regression-information">
                        <div class="label-variance"><a target="_blank" href="https://vi.wikipedia.org/wiki/Ph%C6%B0%C6%A1ng_sai">&#963; = </a></div>
                        <div class="value-variance"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="row wrap-chart-show">
                <div class="col-sm-12 chart-show">
                    <div id="container" style="width:100%; height:500px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{asset('js/highcharts.js')}}"></script>
    <script src="{{asset('js/data.js')}}"></script>
    <script src="{{asset('js/exporting.js')}}"></script>
    <script src="{{asset('js/export-data.js')}}"></script>
    <script src="{{asset('js/highcharts-regression.js')}}"></script>
    <script>
        var dataCO2Root = [
            [0,9396705.835],
            [1,9434402.595],
            [2,9818839.874],
            [3,10355747.346],
            [4,10947007.092],
            [5,11433441.976],
            [6,12009447.002],
            [7,12389685.566],
            [8,13017193.607],
            [9,13797116.836],
            [10,14788798.314],
            [11,15323175.556],
            [12,15957192.522],
            [13,16822109.477],
            [14,16850822.087],
            [15,16745791.873],
            [16,17726098.317],
            [17,18279804.316],
            [18,18497906.475],
            [19,19533547.949],
            [20,19324327.264],
            [21,18726246.898],
            [22,18562354],
            [23,18484356.91],
            [24,19145865.375],
            [25,19719241.162],
            [26,20315356.016],
            [27,20817151.963],
            [28,21565403.313],
            [29,22029385.156],
            [30,22149402.399],
            [31,22403928.869],
            [32,22183417.491],
            [33,22162174.56],
            [34,22551690.634],
            [35,23037524.13],
            [36,23571556.674],
            [37,23975007.348],
            [38,24114192],
            [39,24059187],
            [40,24689911],
            [41,25276631],
            [42,25646998],
            [43,27047792],
            [44,28393581],
            [45,29490014],
            [46,30568112],
            [47,31180501],
            [48,32181592],
            [49,31891899],
            [50,33472376],
            [51,34847501],
            [52,35470891],
            [53,35837591],
            [54,36138285],
        ];
        var modeChart = 'normal';
        var optionChar = {
            regression : false,
            typeChar : 'scatter'
        };
        $(document).ready(function () {
            demoHighChart();
            var equationString = null
            var equation = null;

            $('.select-year-display').change(function () {
                demoHighChart();
            });
            $('.select-display-regression').change(function () {
                if ($(this).val() === 'withRegression'){
                    optionChar.regression = true;
                    demoHighChart();
                    equationString = chart.series[1].name;
                    equation = getEquationByString(equationString);
                    var arrayCO2Real = getArrayDataCO2();
                    var arrayCO2Estimation = getArrayDataCO2Estimation();
                    var MSE = getMSE(arrayCO2Real, arrayCO2Estimation);
                    $(".value-mse").html(MSE);
                    $(".value-variance").html(Math.sqrt(MSE).toFixed(2));
                    $(".regression-information").fadeIn();
                    console.log(equationString);
                    console.log('MSE: ', MSE);
                }
                else {
                    optionChar.regression = false;
                    demoHighChart();
                    $(".regression-information").fadeOut();
                }
            });
            $('#estimation_by_time').click(function () {
                var year = $('#year').val();
                if (year === ''){
                    alert('Enter year estimation');
                    return;
                }
                else if(equationString === null || equation === null){
                    alert('Please select option chart regression');
                    return;
                }
                else {
                    var yearFloat = parseFloat(year);
                    var m = equation.m;
                    var b = equation.b;

                    var pointCO2Estimation = [yearFloat, m * yearFloat + b ];
                    chart.series[0].addPoint(pointCO2Estimation, true, true);
                    chart.series[1].addPoint(pointCO2Estimation, true, true);
                }
            });

        });
        function demoHighChart() {
            var data = getArrayDataCO2();
            var hc_options = {
                chart: {
                    renderTo: 'container',
                    zoomType: 'x'
                },
                title: {
                    text: 'CO2 emissions on the World (1960 - 2014)'
                },
                subtitle: {
                    text: document.ontouchstart === undefined ?
                        'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                },
                xAxis: {
                    title: {
                        text: 'Year'
                    }
                },
                yAxis: {
                    title: {
                        text: 'CO2 emissions (kt)'
                    }
                },
                plotOptions: {
                    area: {
                        fillColor: false,
                        marker: {
                            radius: 1
                        },
                        lineWidth: 1,
                        states: {
                            hover: {
                                lineWidth: 1
                            }
                        },
                        animation: {
                            duration: 0
                        },
                        threshold: null
                    },
                    scatter: {
                        marker: {
                            radius: 8,
                            states: {
                                hover: {
                                    enabled: true,
                                    lineColor: 'rgb(100,100,100)'
                                }
                            }
                        },
                        states: {
                            hover: {
                                marker: {
                                    enabled: false
                                }
                            }
                        },
                        tooltip: {
                            headerFormat: '<b>{series.name} {point.x} (year)</b><br>',
                            pointFormat: '{point.y}'
                        }
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    verticalAlign: 'top',
                    x: 100,
                    y: 100,
                    floating: true,
                    backgroundColor: '#FFFFFF',
                    borderWidth: 1

                },
                series: [
                    {
                        type: optionChar.typeChar,
                        regression: optionChar.regression,
                        name: 'CO2 emissions',
                        color: 'rgba(223, 83, 83, .5)',
                        data: data
                    },
                ]
            };
            chart = new Highcharts.Chart(hc_options);
        }
        function getEquationByString(equationString) {
            var arrayString = equationString.split(" ");
            var m = parseFloat(arrayString[3].substring(0, arrayString[3].length - 1));
            var b = parseFloat(arrayString[5]);
            return {
                m : m,
                b : b,
            }
        }
        function getArrayDataCO2() {
            var seriesReal = [...dataCO2Root];
            seriesReal.length = $('.select-year-display').val();
            return seriesReal;
        }
        function getArrayDataCO2Estimation() {
            var seriesReal = getArrayDataCO2();
            var seriesEstimation =[];
            for (var i = 0; i < seriesReal.length; i++){
                var point = [chart.series[1].data[i].x, chart.series[1].data[i].y];
                seriesEstimation.push(point);
            }
            return seriesEstimation;
        }
        function getMSE(dataReal, dataEstimation) {
            var n = dataReal.length;
            var MSE = 0;
            for(var i = 0; i < n; i++){
                //(y-y') * (y-y')
                MSE += (dataReal[i][1] - dataEstimation[i][1]) * (dataReal[i][1] - dataEstimation[i][1]);
            }
            MSE = MSE / dataReal.length;
            //MSE = Math.sqrt(MSE);
            MSE = parseFloat(MSE.toFixed(2));
            return MSE;
        }
    </script>
@endsection
