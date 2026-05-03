@extends('layouts/default')

@section('title')
    Chimera Print Job #{{ $chimeraPrintJob->id }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.printer.jobs') }}" class="btn btn-primary">{{ trans('general.back') }}</a>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Job Details</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Job ID</th>
                            <td>#{{ $chimeraPrintJob->id }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($chimeraPrintJob->status === 'completed')
                                    <span class="label label-success">{{ ucfirst($chimeraPrintJob->status) }}</span>
                                @elseif($chimeraPrintJob->status === 'failed')
                                    <span class="label label-danger">{{ ucfirst($chimeraPrintJob->status) }}</span>
                                @else
                                    <span class="label label-default">{{ ucfirst($chimeraPrintJob->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $chimeraPrintJob->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>{{ $chimeraPrintJob->user?->present()->fullName ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <th>Delivery Method</th>
                            <td>{{ strtoupper($chimeraPrintJob->delivery_method) }}</td>
                        </tr>
                        <tr>
                            <th>Target</th>
                            <td>
                                @if($chimeraPrintJob->delivery_method === 'tcp')
                                    {{ $chimeraPrintJob->target_host }}:{{ $chimeraPrintJob->target_port }}
                                @else
                                    {{ $chimeraPrintJob->target_path }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Template Path</th>
                            <td>{{ $chimeraPrintJob->template_path ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Asset Count</th>
                            <td>{{ $chimeraPrintJob->asset_count }}</td>
                        </tr>
                        <tr>
                            <th>Result Message</th>
                            <td>{{ $chimeraPrintJob->result_message ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($chimeraPrintJob->payload)
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Payload</h3>
                </div>
                <div class="box-body">
                    <pre style="max-height: 400px; overflow-y: auto;">{{ $chimeraPrintJob->payload }}</pre>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Assets ({{ $chimeraPrintJob->asset_count }})</h3>
                </div>
                <div class="box-body" style="max-height: 500px; overflow-y: auto;">
                    <ul class="list-unstyled">
                        @foreach($chimeraPrintJob->assets as $asset)
                            <li style="padding: 5px 0; border-bottom: 1px solid #eee;">
                                <a href="{{ route('hardware.show', $asset) }}">
                                    {{ $asset->asset_tag }}
                                </a>
                                @if($asset->name)
                                    <br><small class="text-muted">{{ $asset->name }}</small>
                                @endif
                                @if($asset->company)
                                    <br><small class="text-muted">{{ $asset->company->name }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
