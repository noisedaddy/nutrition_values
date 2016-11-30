@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Nutrition Value</div>

                <div class="panel-body">
                    <a href="" data-toggle="modal" data-target="#modal-image">
                        Upload image
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-image" class="modal modal-wide fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <form method="POST" action="{!! url('/tags') !!}" accept-charset="UTF-8">
                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                    <div class="modal-header">
                        <h4 class="modal-title">Upload image</h4>
                    </div>
                    <div class="modal-body">
                        <div id="fileContainer" class="form-group">
                            <input title="Upload" class="btn btn-default photoupload" name="file" type="file" style="left: -203.5px; top: -10px;">
                        </div>
                        <div id="cropContainer" class="form-group hidden">
                            <img id="cropTarget" src="" alt="">
                        </div>
                        <input name="path" type="hidden">
                        <div class="progress hidden">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                <span class="sr-only">0%</span>
                            </div>
                        </div>
                        <div class="div-warning">Maximum file upload size is 2048 kB.</div>
                    </div>

                    <div class="modal-footer" style="text-align: left;">
                        <button name="add" class="btn button_primary hide">Find Food Items</button>
                        <button class="btn button_default" data-dismiss="modal" type="button">Cancel</button>
                        <button name="add-report" class="btn button_primary hide">Full Report</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
