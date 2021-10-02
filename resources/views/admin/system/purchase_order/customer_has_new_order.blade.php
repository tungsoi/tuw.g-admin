<div class="col-md-12" style="border: 1px solid #d2d6de; padding: 10px; text-transform: uppercase;" ><b>{{ $title }}</b></div>
@foreach ($customers as $key => $customer)
    <div class="col-md-2" style="border: 1px solid #d2d6de; padding: 10px; margin: 1px; width: 16.5%">
        <a target="_blank" href="{{ route('admin.purchase_orders.index') }}?customer_id={{ $customer->customer->id }}&status={{ $status }}">
            {{ ($key+1) .". ". $customer->customer->symbol_name }}
        </a>
        &nbsp;
        <span style="color: red">({{ $customer->total }})</span>
    </div>
@endforeach
