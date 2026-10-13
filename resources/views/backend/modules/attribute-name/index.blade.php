@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table theme-table">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($attributeNames as $attributeName)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$attributeName->name}}</td>
                            <td>
                                @if($attributeName->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{$attributeName->created_at->format('Y-m-d')}}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('attribute-name.show', $attributeName->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('attribute-name.edit', $attributeName->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('attribute-name.destroy', $attributeName->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this attribute name?')">
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
                {{$attributeNames->links()}}
            </div>
        </div>
    </div>
@endsection

