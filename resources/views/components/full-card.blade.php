<div class="card full-card mb-2 border-0 p-2">
  @if(isset($cardTitle))
    <div class="card-title bg-white display-4 pr-md-2">
      {{$cardTitle}}
    </div>
  @endif
  <div class="card-body">
    {{$slot}}
  </div>
</div>
