@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table theme-table">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Specification Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($specifications as $specification)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$specification->specifications_name}}</td>
                            <td>
                                @if($specification->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{$specification->created_at->format('Y-m-d')}}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('specification.show', $specification->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('specification.edit', $specification->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('specification.destroy', $specification->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this specification?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{$specifications->links()}}
            </div>
        </div>
    </div>
@endsection

