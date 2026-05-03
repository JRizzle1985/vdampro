@extends('layouts/default')

@section('title')
    {{ trans('general.chimera_printer') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.printer.jobs') }}" class="btn btn-default">Print Jobs</a>
    <a href="{{ route('settings.index') }}" class="btn btn-primary">{{ trans('general.back') }}</a>
@stop

@section('content')
    <form method="POST" action="{{ route('settings.printer.save') }}" accept-charset="UTF-8" id="chimeraSettingsForm" autocomplete="off" class="form-horizontal" role="form">
        @csrf

        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2">
                <div class="panel box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">
                            <x-icon type="labels" />
                            {{ trans('general.chimera_printer') }}
                        </h2>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <div class="col-md-7 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" value="1" name="chimera_enabled" id="chimera_enabled" {{ old('chimera_enabled', $setting->chimera_enabled) ? 'checked="checked"' : '' }}>
                                    {{ trans('general.chimera_printer') }}
                                </label>
                                <p class="help-block">{{ trans('admin/settings/general.printer_help') }}</p>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('chimera_printer_ip') ? ' has-error' : '' }}">
                            <div class="col-md-3 text-right">
                                <label for="chimera_printer_ip" class="control-label">Printer IP Address</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" name="chimera_printer_ip" type="text" id="chimera_printer_ip" value="{{ old('chimera_printer_ip', $setting->chimera_printer_ip) }}">
                                {!! $errors->first('chimera_printer_ip', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('chimera_printer_port') ? ' has-error' : '' }}">
                            <div class="col-md-3 text-right">
                                <label for="chimera_printer_port" class="control-label">Printer Port</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" name="chimera_printer_port" type="number" id="chimera_printer_port" value="{{ old('chimera_printer_port', $setting->chimera_printer_port ?: 1680) }}">
                                {!! $errors->first('chimera_printer_port', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('chimera_delivery_method') ? ' has-error' : '' }}">
                            <div class="col-md-3 text-right">
                                <label for="chimera_delivery_method" class="control-label">Delivery Method</label>
                            </div>
                            <div class="col-md-7">
                                <select class="form-control" name="chimera_delivery_method" id="chimera_delivery_method">
                                    <option value="tcp" {{ old('chimera_delivery_method', $setting->chimera_delivery_method ?: 'tcp') === 'tcp' ? 'selected' : '' }}>TCP (direct)</option>
                                    <option value="file" {{ old('chimera_delivery_method', $setting->chimera_delivery_method) === 'file' ? 'selected' : '' }}>File (network path)</option>
                                </select>
                                {!! $errors->first('chimera_delivery_method', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('chimera_scripts_path') ? ' has-error' : '' }}" id="chimera_scripts_path_group">
                            <div class="col-md-3 text-right">
                                <label for="chimera_scripts_path" class="control-label">Scripts Path</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" name="chimera_scripts_path" type="text" id="chimera_scripts_path" value="{{ old('chimera_scripts_path', $setting->chimera_scripts_path) }}">
                                {!! $errors->first('chimera_scripts_path', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('chimera_qr_prefix') ? ' has-error' : '' }}">
                            <div class="col-md-3 text-right">
                                <label for="chimera_qr_prefix" class="control-label">QR Code Prefix</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" name="chimera_qr_prefix" type="text" id="chimera_qr_prefix" value="{{ old('chimera_qr_prefix', $setting->chimera_qr_prefix) }}">
                                {!! $errors->first('chimera_qr_prefix', '<span class="alert-msg">:message</span>') !!}
                                <p class="help-block">Optional URL or prefix prepended to the asset tag for QR content.</p>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('chimera_template_path') ? ' has-error' : '' }}">
                            <div class="col-md-3 text-right">
                                <label for="chimera_template_path" class="control-label">Default Template Path</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" name="chimera_template_path" type="text" id="chimera_template_path" value="{{ old('chimera_template_path', $setting->chimera_template_path) }}" placeholder="/path/to/template.ykr">
                                {!! $errors->first('chimera_template_path', '<span class="alert-msg">:message</span>') !!}
                                <p class="help-block">Default Chimera template file path for label jobs. Can be overridden per print job.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-7 col-md-offset-3" id="chimeraTestRow">
                                <a class="btn btn-default btn-sm pull-left" id="chimeraTestButton" style="margin-right: 10px;">Test Connection</a>
                                <span id="chimeraTestStatus" data-fallback-message="{{ trans('general.chimera_test_failed', ['error' => 'unknown error']) }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        function toggleChimeraPathField() {
            const deliveryMethod = $('#chimera_delivery_method').val();
            $('#chimera_scripts_path_group').toggle(deliveryMethod === 'file');
        }

        toggleChimeraPathField();
        $('#chimera_delivery_method').on('change', toggleChimeraPathField);

        $('#chimeraTestButton').on('click', function () {
            $('#chimeraTestStatus').removeClass('text-danger text-success').html('<i class="fas fa-spinner spin"></i> Testing connection...');

            $.ajax({
                url: "{{ route('settings.printer.test') }}",
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    chimera_printer_ip: $('#chimera_printer_ip').val(),
                    chimera_printer_port: $('#chimera_printer_port').val()
                },
                dataType: 'json',
                success: function (data) {
                    $('#chimeraTestStatus').removeClass('text-danger').addClass('text-success').html('<i class="fas fa-check"></i> ' + data.message);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : $('#chimeraTestStatus').data('fallback-message');
                    $('#chimeraTestStatus').removeClass('text-success').addClass('text-danger').html('<i class="fas fa-times"></i> ' + message);
                }
            });
        });
    </script>
@stop
