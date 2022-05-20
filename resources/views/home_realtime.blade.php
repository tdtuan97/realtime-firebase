@extends('layouts.master')
@section('content')
    <div class="wrap-container">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <h1 class="m-0 text-dark">Data CO2</h1>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="row group-select">
                <div class="col-sm-3">
                    <div class="small-title text-left">Type chart</div>
                    <select class="form-control select-mode-chart" name="select_mode_char">
                        <option value="area">Area chart</option>
                        <option value="line">Line chart</option>
                        <option value="scatter">Scatter chart</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <div class="small-title text-left">From</div>
                    <div class="input-group date" id="datetime_from" data-target-input="nearest">
                        <input type="text" class="form-control datetimepicker-input" data-target="#datetime_from"/>
                        <div class="input-group-append" data-target="#datetime_from" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 text-left">
                    <div class="row group-button-nav">
                        <a class="btn btn-success" id="btn_get_data_by_time" href="javascript:;">Get data</a>
                        <a class="btn btn-secondary" id="btn_reset_time" href="javascript:;">Refresh</a>
                    </div>
                </div>
                <div class="col-sm-3">
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
    </div>

@endsection
@section('scripts')
    <script src="{{asset('js/highstock.js')}}"></script>
    <script src="{{asset('js/data.js')}}"></script>
    <script src="{{asset('js/exporting.js')}}"></script>
    <script src="{{asset('js/export-data.js')}}"></script>
    <script src="{{asset('js/highcharts-regression.js')}}"></script>
    <script>
        var optionChar = {
            regression : false,
            typeChar : 'area'
        };
        var rootRef;
        var chart = null;
        var onValueChange;
        var onValueChangeChild;
        var configFirebase = {
            apiKey: "AIzaSyBbr_o6TFMgMtxjAG3wofYRnOjfU1euQmg",
            authDomain: "fir-firebase-laravel.firebaseapp.com",
            databaseURL: "https://fir-firebase-laravel.firebaseio.com",
            projectId: "fir-firebase-laravel",
            storageBucket: "fir-firebase-laravel.appspot.com",
            messagingSenderId: "218099479475",
        };
        firebase.initializeApp(configFirebase);
        rootRef = firebase.database().ref('data');
        rootRealTime = firebase.database().ref('realtime-data');
        var date_time_from =  new Date((new Date()).setDate((new Date()).getDate() - 7));
        $('#datetime_from').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            defaultDate:date_time_from
        });
        $(document).ready(function() {
            loadDataRealtimeByTime(getTimeFromInput());
            $('#btn_reset_time').click(function () {
                window.location.reload();
            });
            $('#btn_get_data_by_time').click(function () {
                loadDataRealtimeByTime(getTimeFromInput());
            });
            $('.select-mode-chart').change(function () {
                optionChar.typeChar = $(this).val();
                loadDataRealtimeByTime(getTimeFromInput());
            });
        });
        function process_data_realtime(tvoc, co2, resistance, temperature, timestamp){
            var date_string = timeToStringYYYY_MM_DD(timestamp);
            temperature = fToC(temperature).toFixed(2);
            resistance = (resistance / 100).toFixed(2);
            $('#value-last-update').html(date_string);
            $('#value-tvoc').html(tvoc + ' ppb');
            $('#value-resistance').html(resistance + ' Ω');
            $('#value-temperature').html(temperature + ' °C');
            $('#value-co2').html(co2 + ' ppm');
        }
        function loadDataRealtimeByTime(timestamp_from) {
            rootRef.orderByChild('0').startAt(timestamp_from).once('value')
                .then(function (snapshot) {
                    var array = [];
                    $.each(snapshot.val(), function(key, value) {
                        value.length = 2;
                        array.push(value);
                    });
                    array.pop(array.length);
                    chartRealTime(array);
                });
        }
        function getTimeFromInput() {
            var date_from = $('#datetime_from').data("date");
            return (new Date(date_from)).getTime();
        }
        function chartRealTime(array) {
            // Create the chart
            var hc_options = {
                chart: {
                    renderTo: 'container',
                    zoomType: 'x',
                    events: {
                        load: function () {
                            var series = this.series[0];
                            rootRef.off('value', onValueChangeChild);
                            onValueChangeChild  = rootRef.orderByChild('0').limitToLast(1).on("value", function(snapshot){
                                var array = [];
                                $.each(snapshot.val(), function(key, value) {
                                    array = value;
                                });
                                var timestamp = array[0];
                                var co2 = array[1];
                                var tvoc = array[2];
                                var temperature = array[3];
                                var resistance = array[4];
                                process_data_realtime(tvoc, co2, resistance, temperature, timestamp);
                                var newPoint =  [];
                                newPoint.push(timestamp);
                                newPoint.push(co2);
                                series.addPoint(newPoint, true, true);
                            });
                        }
                    }
                },
                time: {
                    useUTC: false
                },
                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: 'CO2 (ppm)'
                    }
                },
                title: {
                    text: 'Data CO2 Realtime'
                },
                subtitle: {
                    text: document.ontouchstart === undefined ?
                        'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                },
                exporting: {
                    enabled: false
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
                        marker: {
                            radius: 3
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
                    }
                },
                series: [
                    {
                        type: optionChar.typeChar,
                        name: 'Data CO2',
                        data: array,
                    },
                ],
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                align: 'center',
                                verticalAlign: 'bottom',
                                layout: 'horizontal'
                            },
                            yAxis: {
                                labels: {
                                    align: 'left',
                                    x: 0,
                                    y: -5
                                },
                                title: {
                                    text: 'Value CO2'
                                }
                            },
                            subtitle: {
                                text: null
                            },
                            credits: {
                                enabled: false
                            }
                        }
                    }]
                }
            };
            chart = new Highcharts.stockChart(hc_options);
            //chart = new Highcharts.chart(hc_options);
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
            if (date !== ''){
                date_time = new Date(date);
            }
            else {
                date_time = new Date();
            }
            var hour = '';
            var min = '';
            var seconds = '';
            if(date_time.getHours() < 10){
                hour = '0' + date_time.getHours().toString();
            }
            else {
                hour = date_time.getHours().toString();
            }
            if(date_time.getMinutes() < 10){
                min = '0' + date_time.getMinutes().toString();
            }
            else {
                min = date_time.getMinutes().toString();
            }
            if(date_time.getSeconds() < 10){
                seconds = '0' + date_time.getSeconds().toString();
            }
            else {
                seconds = date_time.getSeconds().toString();
            }
            return hour + ':' + min + ':' + seconds;
        }
        function cToF(celsius) {
            var cTemp = celsius;
            var cToFahr = cTemp * 9 / 5 + 32;
            var message = cTemp+'\xB0C is ' + cToFahr + ' \xB0F.';
            return cToFahr;
        }
        function fToC(fahrenheit) {
            var fTemp = fahrenheit;
            var fToCel = (fTemp - 32) * 5 / 9;
            var message = fTemp+'\xB0F is ' + fToCel + '\xB0C.';
            return fToCel;
        }
    </script>
@endsection
