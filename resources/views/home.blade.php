@extends('layouts.master')
@section('content')
    <div class="wrap-container">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <h1 class="m-0 text-dark">Chart Data CO2</h1>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="col-sm-12 row group-select">
                <div class="col-sm-3">
                    <div class="small-title text-left">Date From</div>
                    <div class="input-group date" id="datetime_from" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input" data-target="#datetime_from"/>
                        <div class="input-group-append" data-target="#datetime_from" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="small-title text-left">Date To</div>
                    <div class="input-group date" id="datetime_to" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input" data-target="#datetime_to"/>
                        <div class="input-group-append" data-target="#datetime_to" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="small-title text-left">Estimation CO2 in date</div>
                    <div class="input-group date" id="datetime_estimation" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input"
                               data-target="#datetime_estimation"/>
                        <div class="input-group-append" data-target="#datetime_estimation" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="row group-button-nav">
                        <a class="btn btn-info" id="estimation_by_time" href="javascript:;">Estimation</a>
                        <a class="btn btn-success" id="btn_get_data_by_time" href="javascript:;">Get data</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 row group-select">
                <div class="col-sm-3">
                    <div class="small-title text-left">Type chart</div>
                    <select class="form-control select-mode-chart" name="select_mode_char">
                        <option value="area">Area chart</option>
                        <option value="line">Line chart</option>
                        <option value="scatter">Scatter chart</option>
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
                    <div class="text-left regression-information">
                        <div class="label-mse"><a target="_blank" href="https://vi.wikipedia.org/wiki/Sai_s%E1%BB%91_to%C3%A0n_ph%C6%B0%C6%A1ng_trung_b%C3%ACnh">MSE = </a></div>
                        <div class="value-mse"></div>
                    </div>
                </div>
                <div class="col-sm-3">
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
                    <div id="container" style="width:100%; height:400px;"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <div class="card">
                    <div class="content bg-secondary" id="value-last-update">
                        --:--:--
                    </div>
                    <div class="title">
                        Last update
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card">
                    <div class="content bg-gradient-cyan" id="value-co2">
                        0
                    </div>
                    <div class="title">
                        Data CO2
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card">
                    <div class="content bg-gradient-green" id="value-tvoc">
                        0
                    </div>
                    <div class="title">
                        TVOC
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card">
                    <div class="content bg-gradient-yellow" id="value-temperature">
                        0
                    </div>
                    <div class="title">
                        Temperature
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card">
                    <div class="content bg-gradient-danger" id="value-resistance">
                        0
                    </div>
                    <div class="title">
                        Resistance
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card">
                    <div class="content bg-gradient-success" id="value-alert">
                        Good
                    </div>
                    <div class="title">
                        Status
                    </div>
                </div>
            </div>
        </div>
        <div id="chart-regression" style="height:0px;"></div>
    </div>
@endsection
@section('scripts')
    <script src="{{asset('js/highcharts.js')}}"></script>
    <script src="{{asset('js/data.js')}}"></script>
    <script src="{{asset('js/exporting.js')}}"></script>
    <script src="{{asset('js/export-data.js')}}"></script>
    <script src="{{asset('js/highcharts-regression.js')}}"></script>
    <script>
        var equationString = null;
        var equation = null;
        var pointCO2Estimation = [];
        var modeChart = 'normal';
        var optionChar = {
            regression: false,
            typeChar: 'area'
        };
        var rootRef;
        var chart;
        var onValueChange;
        var configFirebase = {
            apiKey: "AIzaSyBbr_o6TFMgMtxjAG3wofYRnOjfU1euQmg",
            authDomain: "fir-firebase-laravel.firebaseapp.com",
            databaseURL: "https://fir-firebase-laravel.firebaseio.com",
            projectId: "fir-firebase-laravel",
            storageBucket: "fir-firebase-laravel.appspot.com",
            messagingSenderId: "218099479475",
        };
        //var date_time_now = new Date();
        var date_time_from = new Date((new Date()).setDate((new Date()).getDate() - 7));
        var date_time_to = new Date((new Date()).setDate((new Date()).getDate() + 7));
        var datetime_estimation = new Date((new Date()).setDate((new Date()).getDate() + 1));
        $('#datetime_from').datetimepicker({
            // useCurrent: true,
            format: 'YYYY-MM-DD HH:mm:ss',
            defaultDate: date_time_from
        });
        $('#datetime_to').datetimepicker({
            //useCurrent: true,
            format: 'YYYY-MM-DD HH:mm:ss',
            defaultDate: date_time_to
        });
        $('#datetime_estimation').datetimepicker({
            // useCurrent: true,
            format: 'YYYY-MM-DD HH:mm:ss',
            defaultDate: datetime_estimation
        });
        $("#datetime_from").on("change.datetimepicker", function (e) {
            $('#datetime_to').datetimepicker('minDate', e.date);
        });
        $("#datetime_to").on("change.datetimepicker", function (e) {
            $('#datetime_from').datetimepicker('maxDate', e.date);
        });
        firebase.initializeApp(configFirebase);
        rootRef = firebase.database().ref('data');
        $(document).ready(function () {
            var timestamp_from = getTimeFromInput();
            var timestamp_to = getTimeToInput();
            var timestamp_estimation = getTimeToInput();
            loadDataByTime(timestamp_from, timestamp_to);
            $('#btn_reset_time').click(function () {
                window.location.reload();
            });
            $('#btn_get_data_by_time').click(function () {
                timestamp_from = getTimeFromInput();
                timestamp_to = getTimeToInput();
                loadDataByTime(timestamp_from, timestamp_to);
            });
            $('.select-display-regression').change(function () {
                if ($(this).val() === 'withRegression') {
                    optionChar.regression = true;
                } else {
                    optionChar.regression = false;
                }
                $('#btn_get_data_by_time').click();
            });
            $('.select-mode-chart').change(function () {
                optionChar.typeChar = $(this).val();
                loadDataByTime(timestamp_from, timestamp_to);
            });
            $('#estimation_by_time').click(function () {
                timestamp_from = getTimeFromInput();
                timestamp_estimation = getTimeEstimationInput();
                if (timestamp_estimation === 0 || timestamp_estimation === null) {
                    alert('Enter date estimation');
                    return;
                } else if (optionChar.regression === false) {
                    alert('Please select option chart regression');
                    return;
                } else {
                    var datetimeStart = new Date(getTimeFromInput());
                    var datetimeEstimation = new Date(getTimeEstimationInput());
                    var count = 0;
                    while (datetimeEstimation > datetimeStart) {
                        datetimeStart = new Date(datetimeStart.setHours(datetimeStart.getHours() + 1));
                        count++;
                    }

                    var countFloat = parseFloat(count);
                    var m = equation.m;
                    var b = equation.b;

                    var pointCO2Estimation = [timestamp_estimation, m * countFloat + b];
                    chart.series[0].addPoint(pointCO2Estimation, true, true);
                    chart.series[1].addPoint(pointCO2Estimation, true, true);

                    chart.series[1].name = equationString;
                    $(chart.series[1].legendItem.element).children('tspan').text(equationString);
                }
            });
        });

        function getTimeFromInput() {
            var date_from = $('#datetime_from').data("date");
            return (new Date(date_from)).getTime();
        }

        function getTimeToInput() {
            var date_to = $('#datetime_to').data("date");
            return (new Date(date_to)).getTime();
        }

        function getTimeEstimationInput() {
            var date_estimation = $('#datetime_estimation').data("date");
            return (new Date(date_estimation)).getTime();
        }

        function process_data_realtime(tvoc, co2, resistance, temperature, timestamp) {
            var date_string = timeToStringYYYY_MM_DD(timestamp);
            temperature = fToC(temperature).toFixed(2);
            resistance = (resistance / 100).toFixed(2);
            $('#value-last-update').html(date_string);
            $('#value-tvoc').html(tvoc + ' ppb');
            $('#value-resistance').html(resistance + ' Ω');
            $('#value-temperature').html(temperature + ' °C');
            $('#value-co2').html(co2 + ' ppm');
        }

        function loadDataByTime(timestamp_from, timestamp_to) {
            onValueChange = rootRef.on("value", function () {
                rootRef.orderByChild('0').startAt(timestamp_from).endAt(timestamp_to).once('value')
                    .then(function (snapshot) {
                        var array = [];
                        var arrayRegression = [];
                        var count = 0;
                        var lastItem = snapshot.numChildren();
                        $.each(snapshot.val(), function (key, value) {
                            var valueRegression = [];
                            valueRegression.push(count, value[1]);
                            arrayRegression.push(valueRegression);
                            count++;
                            if (count === lastItem) {
                                var timestamp = value[0];
                                var co2 = value[1];
                                var tvoc = value[2];
                                var temperature = value[3];
                                var resistance = value[4];
                                process_data_realtime(tvoc, co2, resistance, temperature, timestamp);
                            }
                            value.length = 2;
                            array.push(value);
                        });
                        dataToHighChart(array);
                        dataToRegression(arrayRegression);
                        equationString = chartRegression.series[1].name;
                        equation = getEquationByString(equationString);
                        if (optionChar.regression === true){
                            chart.series[1].name = equationString;
                            $(chart.series[1].legendItem.element).children('tspan').text(equationString);
                            var arrayCO2Regression = getArrayDataCO2Regression(array.length);
                            var MSE = getMSE(array, arrayCO2Regression);
                            console.log(equationString);
                            $(".value-mse").html(MSE);
                            $(".value-variance").html(Math.sqrt(MSE).toFixed(2));
                            $(".regression-information").fadeIn();
                        }
                        else {
                            $(".regression-information").fadeOut();
                        }
                    });
            });
        }

        function dataToHighChart(data) {
            var hc_options = {
                chart: {
                    renderTo: 'container',
                    zoomType: 'x'
                },
                title: {
                    text: 'Chart Data CO2 Realtime'
                },
                subtitle: {
                    text: document.ontouchstart === undefined ?
                        'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                },
                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: 'CO2 (ppm)'
                    }
                },
                plotOptions: {
                    area: {
                        fillColor: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            },
                            stops: [
                                [0, Highcharts.getOptions().colors[0]],
                                [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                            ]
                        },
                        fillColor: false,
                        lineWidth: 1,
                        states: {
                            hover: {
                                lineWidth: 1
                            }
                        },
                        marker: {
                            radius: 3
                        },
                        animation: {
                            duration: 0
                        },
                        threshold: null
                    }
                },
                time: {
                    timezoneOffset: -7 * 60
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -50,
                    y: 0,
                    floating: true,
                    backgroundColor: '#FFFFFF',
                    borderWidth: 1
                },
                series: [
                    {
                        type: optionChar.typeChar,
                        regression: optionChar.regression,
                        name: 'CO2',
                        data: data
                    },
                ]
            };
            chart = new Highcharts.Chart(hc_options);
        }

        function timeToString(date = '') {
            var date_time;
            if (date !== '') {
                date_time = date;
            } else {
                date_time = new Date();
            }
            var year = date_time.getFullYear().toString();
            var month = '';
            var day = '';
            var hour = '';
            var min = '';
            var seconds = '';
            if (date_time.getMonth() < 10) {
                month = '0' + date_time.getMonth().toString();
            } else {
                month = date_time.getMonth().toString();
            }
            if (date_time.getDate() < 10) {
                day = '0' + date_time.getDate().toString();
            } else {
                day = date_time.getDate().toString();
            }
            if (date_time.getHours() < 10) {
                hour = '0' + date_time.getHours().toString();
            } else {
                hour = date_time.getHours().toString();
            }
            if (date_time.getMinutes() < 10) {
                min = '0' + date_time.getMinutes().toString();
            } else {
                min = date_time.getMinutes().toString();
            }
            if (date_time.getSeconds() < 10) {
                seconds = '0' + date_time.getSeconds().toString();
            } else {
                seconds = date_time.getSeconds().toString();
            }
            return year + '-' + month + '-' + day + ' '
                + hour + ':' + min + ':' + seconds;
        }

        function timeToStringYYYY_MM_DD(date = '') {
            var date_time;
            if (date !== '') {
                date_time = new Date(date);
            } else {
                date_time = new Date();
            }
            var hour = '';
            var min = '';
            var seconds = '';
            if (date_time.getHours() < 10) {
                hour = '0' + date_time.getHours().toString();
            } else {
                hour = date_time.getHours().toString();
            }
            if (date_time.getMinutes() < 10) {
                min = '0' + date_time.getMinutes().toString();
            } else {
                min = date_time.getMinutes().toString();
            }
            if (date_time.getSeconds() < 10) {
                seconds = '0' + date_time.getSeconds().toString();
            } else {
                seconds = date_time.getSeconds().toString();
            }
            return hour + ':' + min + ':' + seconds;
        }

        function cToF(celsius) {
            var cTemp = celsius;
            var cToFahr = cTemp * 9 / 5 + 32;
            var message = cTemp + '\xB0C is ' + cToFahr + ' \xB0F.';
            return cToFahr;
        }

        function fToC(fahrenheit) {
            var fTemp = fahrenheit;
            var fToCel = (fTemp - 32) * 5 / 9;
            var message = fTemp + '\xB0F is ' + fToCel + '\xB0C.';
            return fToCel;
        }

        function getEquationByString(equationString) {
            var arrayString = equationString.split(" ");
            var m = parseFloat(arrayString[3].substring(0, arrayString[3].length - 1));
            var b = parseFloat(arrayString[5]);
            return {
                m: m,
                b: b,
            }
        }
        function getArrayDataCO2Regression(lenght) {
            var seriesEstimation =[];
            for (var i = 0; i < lenght; i++){
                var point = [chartRegression.series[1].data[i].x, chartRegression.series[1].data[i].y];
                seriesEstimation.push(point);
            }
            return seriesEstimation;
        }
        function getMSE(dataReal, dataEstimation) {
            var n = dataReal.length;
            var MSE = 0;
            for(var i = 0; i < n; i++){
                //(y-y')
                MSE += (dataReal[i][1] - dataEstimation[i][1]) * (dataReal[i][1] - dataEstimation[i][1]);
            }
            MSE = MSE / dataReal.length;
            //MSE = Math.sqrt(MSE);
            MSE = parseFloat(MSE.toFixed(2));
            return MSE;
        }
        function dataToRegression(data) {
            var hc_options = {
                chart: {
                    renderTo: 'chart-regression',
                },
                series: [
                    {
                        type: 'line',
                        regression: true,
                        name: 'CO2',
                        data: data
                    },
                ]
            };
            chartRegression = new Highcharts.Chart(hc_options);
        }
    </script>
@endsection
