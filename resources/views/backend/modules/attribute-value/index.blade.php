@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table theme-table">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Attribute Name</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($attributeValues as $attributeValue)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$attributeValue->attributeName->name ?? 'N/A'}}</td>
                            <td>{{$attributeValue->name}}</td>
                            <td>
                                @if($attributeValue->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{$attributeValue->created_at->format('Y-m-d')}}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('attribute-value.show', $attributeValue->id)}}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{route('attribute-value.edit', $attributeValue->id)}}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{route('attribute-value.destroy', $attributeValue->id)}}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this attribute value?')">
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
                {{$attributeValues->links()}}
            </div>
        </div>
    </div>
@endsection

