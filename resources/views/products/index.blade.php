@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route('product.filter') }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" value="{{ request('title') }}" name="title" placeholder="Product Title"
                        class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">-- SELECT A VARIANT</option>
                        @foreach ($Variant as $va)
                            <optgroup label="{{ $va->title }}">
                                @foreach ($va->ProductVariant as $pv)
                                    <option value="{{ $pv->variant }}" @if (request('variant') == $pv->variant) selected @endif>
                                        {{ $pv->variant }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" value="{{ request('price_from') }}" aria-label="First name"
                            placeholder="From" class="form-control">
                        <input type="text" name="price_to" value="{{ request('price_to') }}" aria-label="Last name"
                            placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">

                    <input type="date" name="date" min="{{ $firstProduct->created_at->format('Y-m-d') }}"
                        max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" value="{{ request('date') }}"
                        placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th width="40%">Description</th>
                            <th>Variant</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $i = $product->perPage() * ($product->currentPage() - 1) + 1;
                        @endphp
                        @foreach ($product as $p)
                            @if (count($p->product_variant_prices) > 0 && count($p->ProductVariant) > 0)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $p->title }} <br> Created at : {{ $p->created_at->format('d-M-Y') }}</td>
                                    <td>{{ $p->description }}</td>
                                    <td>
                                        <dl class="row mb-0" style="height: 100vh; overflow: hidden"
                                            id="variant-{{ $p->id }}">

                                            <dt class="col-sm-3 pb-0">
                                                @if ($is_filtered == 0)
                                                    @foreach ($p->product_variant_prices as $value)
                                                        @if (!is_null($value->ProductVariant('product_variant_one')))
                                                            {{ $value->ProductVariant('product_variant_one')->variant }}
                                                        @endif
                                                        /
                                                        @if (!is_null($value->ProductVariant('product_variant_two')))
                                                            {{ $value->ProductVariant('product_variant_two')->variant }}
                                                        @endif
                                                        /
                                                        @if ($value->ProductVariant('product_variant_three'))
                                                            {{ $value->ProductVariant('product_variant_three')->variant }}
                                                        @endif
                                                        <br>
                                                    @endforeach
                                                @else
                                                    @foreach ($p->ProductVariant as $value)
                                                        @foreach ($value->ProductVariantPrices() as $val)
                                                            @if (!is_null($val->ProductVariant('product_variant_one')))
                                                                {{ $val->ProductVariant('product_variant_one')->variant }}
                                                            @endif
                                                            /
                                                            @if (!is_null($val->ProductVariant('product_variant_two')))
                                                                {{ $val->ProductVariant('product_variant_two')->variant }}
                                                            @endif
                                                            /
                                                            @if ($val->ProductVariant('product_variant_three'))
                                                                {{ $val->ProductVariant('product_variant_three')->variant }}
                                                            @endif
                                                            <br>
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                            </dt>
                                            <dd class="col-sm-9">
                                                <dl class="row mb-0">
                                                    @if ($is_filtered == 0)
                                                        @foreach ($p->product_variant_prices as $value)
                                                            <dt class="col-sm-4 pb-0">
                                                                Price {{ number_format($value->price, 2) }}
                                                            </dt>
                                                            <dd class="col-sm-8 pb-0">InStock :
                                                                {{ number_format($value->stock, 2) }}</dd>
                                                        @endforeach
                                                    @else
                                                        @foreach ($p->ProductVariant as $value)
                                                            @foreach ($value->ProductVariantPrices() as $val)
                                                                <dt class="col-sm-4 pb-0">
                                                                    Price
                                                                    {{ number_format($val->price, 2) }}
                                                                </dt>
                                                                <dd class="col-sm-8 pb-0">InStock :
                                                                    {{ number_format($val->stock, 2) }}
                                                                </dd>
                                                            @endforeach
                                                        @endforeach
                                                    @endif
                                                </dl>
                                            </dd>
                                        </dl>
                                        <button onclick="$('#variant-{{ $p->id }}').toggleClass('h-auto')"
                                            class="btn btn-sm btn-link">Show
                                            more</button>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('product.edit', $p->id) }}" class="btn btn-success">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ $product->firstitem() }} to {{ $product->lastitem() }} out of {{ $product->total() }}
                    </p>
                </div>
                <div class="col-md-2">
                    {!! $product->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
