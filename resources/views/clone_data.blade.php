@extends('layouts.master')
@section('content')
    <div class="wrap-container">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <h1 class="m-0 text-dark">Clone data</h1>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="col-sm-12 row group-select">
                <div class="col-sm-3">
                    <div class="small-title text-left">From</div>
                    <div class="form-group">
                        <div class="input-group date" id="datetime_from" data-target-input="nearest">
                            <input type="text" class="form-control datetimepicker-input" data-target="#datetime_from"/>
                            <div class="input-group-append" data-target="#datetime_from" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="small-title text-left">To</div>
                    <div class="form-group">
                        <div class="input-group date" id="datetime_to" data-target-input="nearest">
                            <input type="text" class="form-control datetimepicker-input" data-target="#datetime_to"/>
                            <div class="input-group-append" data-target="#datetime_to" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="row group-button-nav">
                        <a class="btn btn-success" id="clone_data" href="javascript:;">Clone Data</a>
                        <a class="btn btn-danger" id="remove_data" href="javascript:;">Remove data</a>
                    </div>
                </div>
                <div class="col-sm-3">
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
       // let date_time_from =  new Date();
        let date_time_from =  new Date((new Date()).setMonth((new Date()).getMonth() - 1));
        let date_time_to =  new Date((new Date()).setMonth((new Date()).getMonth() + 1));
        $('#datetime_from').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            defaultDate:date_time_from
        });
        $('#datetime_to').datetimepicker({
            //useCurrent: true,
            format: 'YYYY-MM-DD HH:mm:ss',
            defaultDate:date_time_to
        });
        $("#datetime_from").on("change.datetimepicker", function (e) {
            $('#datetime_to').datetimepicker('minDate', e.date);
        });
        $("#datetime_to").on("change.datetimepicker", function (e) {
            $('#datetime_from').datetimepicker('maxDate', e.date);
        });
        let rootRef;
        let chart = null;
        let onValueChange;
        let onValueChangeChild;
        let configFirebase = {
            apiKey: "AIzaSyBbr_o6TFMgMtxjAG3wofYRnOjfU1euQmg",
            authDomain: "fir-firebase-laravel.firebaseapp.com",
            databaseURL: "https://fir-firebase-laravel.firebaseio.com",
            projectId: "fir-firebase-laravel",
            storageBucket: "fir-firebase-laravel.appspot.com",
            messagingSenderId: "218099479475",
        };
        firebase.initializeApp(configFirebase);
        rootRef = firebase.database().ref('data');
        $(document).ready(function() {
            //setInterval(addStore, 60 * 60 * 1000);
            $('#remove_data').click(function () {
                let result = confirm("Delete data!");
                if (result) {
                    rootRef.orderByChild('0').startAt(getTimeFromInput()).endAt(getTimeToInput()).once('value')
                        .then(function (snapshot) {
                            $.each(snapshot.val(), function(key, value) {
                                rootRef.child(key).remove();
                            });
                        });
                    alert('Success');
                }
            });
            $('#clone_data').click(function () {
                let date_from = $('#datetime_from').data("date");
                date_from = new Date(date_from);
                addStore(date_from);
                alert('Success');
            });
            rootRef.orderByChild('0').limitToLast(1).once('value')
                .then(function (snapshot) {
                    $.each(snapshot.val(), function(key, value) {
                        let array = [];
                        $.each(snapshot.val(), function(key, value) {
                            array = value;
                        });
                        let timestamp = array[0];
                        let co2 = array[1];
                        let tvoc = array[2];
                        let temperature = array[3];
                        let resistance = array[4];
                        process_data_realtime(tvoc, co2, resistance, temperature, timestamp);
                    });
                });
        });
        function addStore(datetimeStart){
            let datetime_now = new Date();
            //console.log(timestamp_now);
            //console.log(timeStart);
            let arrayTimestamp = [];
            while (datetime_now > datetimeStart) {
                datetimeStart = new Date(datetimeStart.setHours(datetimeStart.getHours() + 1));
                arrayTimestamp.push(datetimeStart.getTime());
            }
            for(let i = 0; i < arrayTimestamp.length; i++){
                let co2 = 420 + Math.floor(Math.random() * 300);
                let tvoc = 25 + Math.floor(Math.random() * 15);
                let temperature = 70 + Math.floor(Math.random() * 10);
                let resistance = 1400 + Math.floor(Math.random() * 300);
                let newStoreRef = rootRef.push();
                newStoreRef.set({
                    0: arrayTimestamp[i],
                    1: co2,
                    2: tvoc,
                    3: temperature,
                    4: resistance
                });
            }
            /*let newStoreRef = rootRef.push();
            let timestamp = {'.sv' : 'timestamp'};
            newStoreRef.set({
                0: timestamp,
                1: co2,
                2: tvoc,
                3: temperature,
                4: resistance
            });
            rootRef.orderByChild('0').limitToLast(1).once('value')
                .then(function (snapshot) {
                    $.each(snapshot.val(), function(key, value) {
                        let array = [];
                        $.each(snapshot.val(), function(key, value) {
                            array = value;
                        });
                        let timestamp = array[0];
                        let co2 = array[1];
                        let tvoc = array[2];
                        let temperature = array[3];
                        let resistance = array[4];
                        process_data_realtime(tvoc, co2, resistance, temperature, timestamp);
                    });
                });*/
        }
        function process_data_realtime(tvoc, co2, resistance, temperature, timestamp){
            let date_string = timeToStringYYYY_MM_DD(timestamp);
            temperature = fToC(temperature).toFixed(2);
            resistance = (resistance / 100).toFixed(2);
            $('#value-last-update').html(date_string);
            $('#value-tvoc').html(tvoc + ' ppb');
            $('#value-resistance').html(resistance + ' Ω');
            $('#value-temperature').html(temperature + ' °C');
            $('#value-co2').html(co2 + ' ppm');
        }
        function timeToStringYYYY_MM_DD(date = '') {
            let date_time;
            if (date !== ''){
                date_time = new Date(date);
            }
            else {
                date_time = new Date();
            }
            let hour = '';
            let min = '';
            let seconds = '';
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
        function cToF(celsius)
        {
            let cTemp = celsius;
            let cToFahr = cTemp * 9 / 5 + 32;
            let message = cTemp+'\xB0C is ' + cToFahr + ' \xB0F.';
            return cToFahr;
        }

        function fToC(fahrenheit)
        {
            let fTemp = fahrenheit;
            let fToCel = (fTemp - 32) * 5 / 9;
            let message = fTemp+'\xB0F is ' + fToCel + '\xB0C.';
            return fToCel;
        }
        function getTimeFromInput() {
            let date_from = $('#datetime_from').data("date");
            return (new Date(date_from)).getTime();
        }
        function getTimeToInput() {
            let date_to = $('#datetime_to').data("date");
            return (new Date(date_to)).getTime();
        }
    </script>
@endsection
