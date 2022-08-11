@extends("master.authenticated", ["user" => $user])

@section('title', __('مساعدة - ').$sections[$section])

@section('contents')
    @component('components.full-card')
        @slot('cardTitle')
            {{$sections[$section]}}
        @endslot

        <div class="d-sm-none d-block">
            <select class="form-control" name="section">
                @foreach($sections as $key => $s)
                    <option value="{{$key}}" {{$section == $key ? 'selected' : ''}}>{{$s}}</option>
                @endforeach
            </select>
        </div>
        <div class="p-2 row">
            <div class="col-sm-9 col-12">
                @component('components.help.'.$section, ['user' => $user])
                @endcomponent
            </div>
            <div class="col-sm-3 d-sm-block d-none">
                <div class="list-group">
                    @foreach($sections as $key => $s)
                        <a class="list-group-item list-group-item-action{{$key == $section ? ' active' : ''}}"
                           href="/help/{{$key}}">{{$s}}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('scripts')
    <script src="/js/help.js?v=8"></script>
@endpush