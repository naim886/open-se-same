@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? null }}</legend>
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Specification Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">ID</th>
                                        <td>{{$specification->id}}</td>
                                    </tr>
                                    <tr>
                                        <th>Specification Name</th>
                                        <td>{{$specification->specifications_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($specification->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{$specification->created_at->format('Y-m-d H:i:s')}}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{$specification->updated_at->format('Y-m-d H:i:s')}}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4 justify-content-center">
                    <div class="col-lg-6">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{route('specification.edit', $specification->id)}}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="{{route('specification.index')}}" class="btn btn-secondary">
                                <i class="fa fa-list"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
@endsection

