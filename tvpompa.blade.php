@extends('layouts.colorwhitereport')
<!-- ------------------------------------------------------------------------------- -->
@section('title') TV Pompa @stop
<!-- ------------------------------------------------------------------------------- -->

@section('navbar_right')
    <p class="pointer" id="sp-Time" ng-click="oSearch()" style="font-size: 20px;font-weight: bold;white-space: nowrap;">
        <span class="real-day"></span>, <span class="real-date"></span>, <span class="real-time"></span> <i
            class="fa fa-spin fa-spinner"></i>
    </p>
@stop
@section('content')
    <script type="text/javascript" src="{{ url('coloradmin') }}/assets/plugins/fusioncharts-suite-xt/js/fusioncharts.js">
    </script>
    <script type="text/javascript"
        src="{{ url('coloradmin') }}/assets/plugins/fusioncharts-suite-xt/integrations/angularjs/js/angular-fusioncharts.min.js">
    </script>
    <script type="text/javascript"
        src="{{ url('coloradmin') }}/assets/plugins/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>

    <div class="row" style="margin-bottom: 5px;">
        <div class="col-sm-4">
            <div class="panel panel-success" style="margin: 0px;">
                <div class="panel-heading p-5" style="font-size: 20px;" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-dashboard"></i> RECAP
                </div>
                <div class="panel-body p-0 text-center">
                    <div style="max-height: 340px;">
                        <table ng-table="tableList" show-filter="false" class="table table-condensed table-hover"
                            style="white-space: nowrap;">
                            <tr ng-repeat="v in $data">
                                <td title="'Tanggal'" filter="{tanggal: 'text'}" style="margin: 0; padding: 3px;" sortable="'tanggal'">@{{ v.tanggal.split(' ')[0] }}
                                </td>
                                <td title="'Durasi'" filter="{durasi: 'text'}" style="margin: 0; padding: 3px;" sortable="'durasi'">@{{ v.durasi }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="panel panel-success" style="margin: 0px;">
                <div class="panel-heading p-5" style="font-size: 20px;" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-dashboard"></i> CONDITION
                </div>
                <div class="panel-body p-0 text-center">
                    <div style="height: 300px;">
                        <label class="text-bold" style="font-size: 20px;padding: 0px;margin-top: 20px;">STATUS
                            POMPA:</label><br />
                        <span class="text-bold  blink_me"
                            style="font-size: 100px; margin: 0px; padding: 0px;
                                   @if ($last[0]->status == 'MALF') color: red; @elseif($last[0]->status == 'ON') color: green; @endif">
                            {{ $last[0]->status }}
                        </span><br>
                        <label class="text-bold" style="font-size: 20px;padding: 0px;margin-top: 20px;">Waktu Mulai :
                            {{ $last[0]->waktu }}</label><br />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="panel panel-success" style="margin: 0px;">
                <div class="panel-heading p-5" style="font-size: 20px;" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-dashboard"></i> DIAGRAM BULANAN
                </div>
                <div class="panel-body p-0 text-center">
                    <div style="height: 440px;">
                        <div fusioncharts width="100%" height="440" type="column2d" id="monthly-chart"
                            dataSource='{
                    "chart": {
                        "caption": "Total Durasi Harian",
                        "xAxisName": "Tanggal",
                        "yAxisName": "Total Durasi (menit)",
                        "theme": "fusion"
                    },
                    "data": {{ $chartDataJson }}
                }'
                            class="p-0"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- <div class="panel panel-success">
    <div class="panel-heading">
        @component('layouts.common.coloradmin.panel_button') @endcomponent @yield('breadcrumb')
    </div>
    <div class="panel-body">
        <div class="m-b-5 form-inline">
            <div class="pull-right">
                <div ng-show="f.tab=='list'">
                    @component('layouts.common.coloradmin.guide', ['tag' => 'trs_tv_pompa']) @endcomponent
                    <div class="input-group">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" ng-click="oPrintTable()"><i class="fa fa fa-print"></i></button>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" ng-model="f.q" ng-enter="oSearch()" placeholder="Search">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-success btn-sm" ng-click="oSearch()"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
               
            </div>
           
        </div>
        <br>
        <div ng-show="f.tab=='list'">
            <div class="alert alert-warning" ng-show="f.trash==1"><i class="fa fa-warning fa-2x"></i> This is deleted item<br>Trashed</div>
            <div id="div1" class="table-responsive">
                <table ng-table="tableList" show-filter="false" class="table table-condensed table-hover" style="white-space: nowrap;">
                    <tr ng-repeat="v in $data" class="pointer" ng-click="oShow(v.token)">
                        <td title="'Id'" filter="{id: 'text'}" sortable="'id'">@{{ v.id }}</td>
                        <td title="'Tanggal'" filter="{tanggal: 'text'}" sortable="'tanggal'">@{{ v.tanggal }}</td>
                        <td title="'Durasi'" filter="{durasi: 'text'}" sortable="'durasi'">@{{ v.durasi }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
    </div>
</div> --}}

    <style type="text/css">
        .ng-table thead tr th {
            text-align: center
        }

        .content {
            padding: 5px 15px;
        }

        .row>.col-sm-6 {
            padding: 0px 3px;
        }

        .panel {
            border-radius: 0px;
        }

        .blink_me {
            animation: blinker 1s linear infinite;
        }

        .ng-table-counts {
            display: none;
        }

        .ng-table-pager {
            display: none;
        }

        @keyframes blinker {
            50% {
                opacity: 0.5;
            }
        }

        @media only screen and (max-width: 768px) {

            /* For mobile phones: */
            li a {
                font-size: 3.5vw;
            }

            #sp-Time {
                font-size: 3vw !important;
            }

            .navbar-brand {
                font-size: 3.1vw !important;
            }

            .panel-heading {
                font-size: 3.1vw !important;
            }
        }
    </style>
    <script>
        $(document).ready(function() {
            var interval = setInterval(function() {
                moment.locale('id');
                var momentNow = moment();
                $('.real-day').html(momentNow.format('dddd'));
                $('.real-date').html(momentNow.format('DD MMMM YYYY'));
                $('.real-time').html(momentNow.format('HH:mm:ss'));
            }, 100);
        });
        app.requires.push('ng-fusioncharts');
        app.controller('mainCtrl', ['$scope', '$http', 'NgTableParams', 'SfService', 'FileUploader', function($scope, $http,
            NgTableParams, SfService, FileUploader) {
            SfService.setUrl("{{ url('trs_tv_pompa') }}");
            $scope.f = {
                crud: 'c',
                tab: 'list',
                trash: 0,
                userid: "{{ Auth::user()->userid }}",
                plant: "{{ Session::get('plant') }}"
            };
            $scope.h = {};
            $scope.m = [];

            $scope.dchart1 = {};

            var uploader = $scope.uploader = new FileUploader({
                url: "{{ url('upload_file') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                onBeforeUploadItem: function(item) {
                    //s pattern : t : text, i : image,a : audio, v : video, p : application, x : all mime
                    item.formData = [{
                        id: $scope.h.id,
                        path: 'trs_tv_pompa',
                        s: 'i',
                        userid: $scope.f.userid,
                        plant: $scope.f.plant
                    }];
                },
                onSuccessItem: function(fileItem, response, status, headers) {
                    $scope.oGallery();
                }
            });

            $scope.oGallery = function() {
                SfGetMediaList('trs_tv_pompa/' + $scope.h.id, function(jdata) {
                    $scope.m = jdata.files;
                    $scope.$apply();
                });
            }

            $scope.oNew = function() {
                $scope.f.tab = 'frm';
                $scope.f.crud = 'c';
                $scope.h = {};
                $scope.m = [];
                SfFormNew("#frm");
            }

            $scope.oCopy = function() {
                $scope.f.crud = 'c';
                $scope.h.id = null;
            }


            $scope.oSearch = function(trash, order_by) {
                $scope.f.tab = "list";
                $scope.f.trash = trash;
                // Initialize _waktu or fetch it from a service
               
                $scope.tableList = new NgTableParams({}, {
                    getData: function($defer, params) {
                        var $btn = $('button').button('loading');
                        return $http.get(SfService.getUrl("_list"), {
                            params: {
                                page: $scope.tableList.page(),
                                order_by: $scope.tableList.orderBy(),
                                q: $scope.f.q,
                                trash: $scope.f.trash,
                                plant: $scope.f.plant,
                                userid: $scope.f.userid
                            }
                        }).then(function(jdata) {
                            $btn.button('reset');
                            $scope.tableList.total(jdata.data.data.total);
                            return jdata.data.data.data;
                        }, function(error) {
                            $btn.button('reset');
                            swal('', error.data, 'error');
                        });
                    }
                });
            }

            var interval = setInterval(function() {
                $scope.oSearch();
                //console.log("Test");
            }, 1000 * 60 * 10);

            var interval = setInterval(function() {
                location.reload();
            }, 60000);
            $scope.oSearch();
            
            $scope.oSave = function() {
                SfService.save("#frm", SfService.getUrl(), {
                    h: $scope.h,
                    f: $scope.f
                }, function(jdata) {
                    $scope.oSearch();
                });
            }

            $scope.oShow = function(token) {
                SfService.show(SfService.getUrl("/" + encodeURI(token) + "/edit"), {}, function(jdata) {
                    $scope.oNew();
                    $scope.h = jdata.data.h;
                    $scope.f.crud = 'u';
                    $scope.oGallery();
                    if (chatCtrl() != undefined) {
                        chatCtrl().listChat();
                    }
                });
            }

            $scope.oDel = function(token, isRestore) {
                if (token == undefined) {
                    var token = $scope.h.token;
                }
                SfService.delete(SfService.getUrl("/" + encodeURI(token)), {
                    restore: isRestore
                }, function(jdata) {
                    $scope.oSearch();
                });
            }

            $scope.oRestore = function(id) {
                $scope.oDel(id, 1);
            }

            $scope.oLookup = function(id, selector, obj) {
                switch (id) {
                    /*case 'parent':
                        SfLookup(SfService.getUrl("_lookup"), function(id, name, jsondata) {
                            $("#" + selector).val(id).trigger('input');;
                        });
                        break;*/
                    default:
                        swal('Sorry', 'Under construction', 'error');
                        break;
                }
            }

            $scope.oLog = function() {
                SfLog('trs_tv_pompa', $scope.h.id);
            }

            $scope.oPrint = function() {
                window.open(SfService.getUrl('_print') + "/" + '?token=' + $scope.h.token);
            }

            $scope.oSearch();
        }]);
    </script>
@endsection
