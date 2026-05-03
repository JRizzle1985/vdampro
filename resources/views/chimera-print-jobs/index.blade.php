@extends('layouts/default')

@section('title')
    Chimera Print Jobs
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.printer.index') }}" class="btn btn-primary">{{ trans('general.back') }}</a>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Chimera Print Jobs</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Assets</th>
                                    <th>Template</th>
                                    <th>Delivery</th>
                                    <th>Target</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                    <tr>
                                        <td>
                                            <a href="{{ route('settings.printer.jobs.show', $job) }}">
                                                #{{ $job->id }}
                                            </a>
                                        </td>
                                        <td>{{ $job->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $job->user?->present()->fullName ?? 'System' }}</td>
                                        <td>
                                            @if($job->status === 'completed')
                                                <span class="label label-success">{{ ucfirst($job->status) }}</span>
                                            @elseif($job->status === 'failed')
                                                <span class="label label-danger">{{ ucfirst($job->status) }}</span>
                                            @else
                                                <span class="label label-default">{{ ucfirst($job->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $job->asset_count }}</td>
                                        <td>{{ $job->template_path ?? '-' }}</td>
                                        <td>{{ strtoupper($job->delivery_method) }}</td>
                                        <td>
                                            @if($job->delivery_method === 'tcp')
                                                {{ $job->target_host }}:{{ $job->target_port }}
                                            @else
                                                {{ $job->target_path }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pull-right">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
