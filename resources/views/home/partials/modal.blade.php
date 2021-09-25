<!-- Modal -->
@if (isset($alert) && $alert)
<div id="alert-mdl" class="modal" role="dialog">
    <div class="modal-dialog">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          {{-- <button type="button" class="close" data-dismiss="modal">&times;</button> --}}
          <h4 class="modal-title">{{ $alert['title'] }}</h4>
        </div>
        <div class="modal-body">
          {!! $alert['content'] !!}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Đóng</button>
        </div>
      </div>
  
    </div>
</div>

@endif