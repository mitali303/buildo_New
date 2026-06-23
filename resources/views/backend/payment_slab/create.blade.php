@extends('backend.partials.master')

@section('title', !empty($payslab) ? 'Edit Payment Slab' : 'Create Payment Slab')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($payslab) ? 'Edit Payment Slab' : 'Create Payment Slab' }}</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($payslab) ? route('PaymentSlab.update')
                                                         : route('PaymentSlab.store') }}"
                              method="POST">
                            @csrf
                            @if(!empty($payslab))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $payslab->ID }}">
                            @endif

                            <div class="row">
                                @php
                                    $cols = ['pilnth','slab','bricks','plaster','floaring','plumbing','project'];
                                @endphp

                                @foreach ($cols as $c)
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label text-capitalize">{{ $c }} <span class="text-danger">*</span></label>
                                        <input  type="number" step="0.01" min="0"
                                                name="{{ $c }}"
                                                id="{{ $c }}"
                                                class="form-control calc-field @error($c) is-invalid @enderror"
                                                value="{{ old($c, $payslab->$c ?? '') }}">
                                        @error($c) <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                @endforeach

                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Total</label>
                                    <input type="number" step="0.01" min="0"
                                           name="total"
                                           id="total"
                                           class="form-control"
                                           value="{{ old('total', $payslab->total ?? '') }}" readonly>
                                         @error('total')
                                            <div class="text-danger mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($payslab) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('PaymentSlab') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const fields = ['pilnth','slab','bricks','plaster','floaring','plumbing','project'];
    const total  = document.getElementById('total');

    function calc() {
        let sum = 0;
        fields.forEach(f => {
            sum += parseFloat(document.getElementById(f).value) || 0;
        });
        total.value = sum.toFixed(2);
    }

    /* onchange for each numeric field */
    fields.forEach(f => {
        document.getElementById(f).addEventListener('change', calc);
    });

    calc();  // initial total on page load
});
</script>
@endpush

